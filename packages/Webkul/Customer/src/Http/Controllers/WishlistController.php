<?php

namespace Webkul\Customer\Http\Controllers;

use Webkul\Product\Repositories\ProductRepository;
use Webkul\Customer\Repositories\WishlistRepository;
use Webkul\Checkout\Contracts\Cart as CartResource;
use Webkul\Customer\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Webkul\Customer\Helpers\BasecampMail;
use Cart;
use Mail;
use Webkul\Customer\Mail\WishlistEmail;

class WishlistController extends Controller
{
    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * WishlistRepository object
     *
     * @var \Webkul\Customer\Repositories\WishlistRepository
    */
    protected $wishlistRepository;

    /**
     * WishlistRepository object
     *
     * @var \Webkul\Product\Repositories\ProductRepository
    */
    protected $productRepository;
	
	/**
     * CustomerRepository object
     *
     * @var \Webkul\Customer\Repositories\CustomerRepository
     */
    protected $customerRepository;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\Customer\Repositories\WishlistRepository  $wishlistRepository
     * @param  \Webkul\Product\Repositories\ProductRepository  $productRepository
     * @return void
     */
    public function __construct(
        WishlistRepository $wishlistRepository,
		CustomerRepository $customerRepository,
        ProductRepository $productRepository
    )
    {
        $this->middleware('customer')->only(['moveToWishlist']);

        $this->_config = request('_config');

        $this->wishlistRepository = $wishlistRepository;
		
		$this->customerRepository = $customerRepository;

        $this->productRepository = $productRepository;
    }

    /**
     * Displays the listing resources if the customer having items in wishlist.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $results = [];$total=0;
		$results['engagement'] = $this->wishlistRepository->getCustomerWhishlist('19');
		$results['wedding'] = $this->wishlistRepository->getCustomerWhishlist('17');
		$results['jewelry'] = $this->wishlistRepository->getCustomerWhishlist('13,16,18');
		$results['diamond'] = $this->wishlistRepository->getCustomerWhishlist('2');

		return view($this->_config['view'])->with('items', $results ? $results : null);
		
		/*$wishlistItems = $this->wishlistRepository->getCustomerWhishlist();
        return view($this->_config['view'])->with('items', $wishlistItems);*/
    }
	
	/**
     * Displays the listing resources if the guest having items in wishlist.
     *
     * @return \Illuminate\View\View
     */
    public function guestUser()
    {
        $wish = $this->getWishUserCookie();
		
		if (! $wish && ! $wish = $this->createWishUser()) {
            // echo "create"; exit();
            return ['warning' => trans('customer::app.wishlist.failure')];
        }
		
		foreach($wish as $userId) {
			$getUserId = $userId->id;
		}
				
		//$wishlistItems = $this->wishlistRepository->getGuestWhishlist($getUserId);
		$results = [];
		$results['engagement'] = $this->wishlistRepository->getGuestWhishlist($getUserId,'19');
		$results['wedding'] = $this->wishlistRepository->getGuestWhishlist($getUserId,'17');
		$results['jewelry'] = $this->wishlistRepository->getGuestWhishlist($getUserId,'13,16,18');
		$results['diamond'] = $this->wishlistRepository->getGuestWhishlist($getUserId,'2');

        //return view($this->_config['view'])->with('items', $wishlistItems);
		return view($this->_config['view'])->with('items', $results ? $results : null);
    }
	
	public function guestUserLogin(Request $request)
	{
		session()->flash('wishlistController',true);
		$data = request()->input();
		$validator = Validator::make($request->all(), [
            'email'      => 'email|required',
        ]);
		
		if (!$validator->fails()) {
				//
		} else {
			redirect()->back()->with('error_msg', $validator->messages());
			return redirect()->back()->with('email', $data['email']);
		}
		
		$customer = $this->customerRepository->findOneByField('email', $data['email']);
	
		if(isset($customer->is_verified) && $customer->is_verified == 1)
		{
			session()->flash('info', __('Please Sign in your account to save your wish list'));
			return redirect()->route('customer.session.index')->with('email', $data['email']);
		} else {
			session()->flash('info', __('Please Create an Account to save your wish list'));
			return redirect()->route('customer.session.index')->with('ac_email', $data['email']);
		}
	}

    /**
     * Function to add item to the wishlist.
     *
     * @param  int  $itemId
     * @return \Illuminate\Http\Response
     */
    public function add($itemId)
    {
        $product = $this->productRepository->findOneByField('id', $itemId);

        if (! $product->status)
            return redirect()->back();

        $data = [
            'channel_id'  => core()->getCurrentChannel()->id,
            'product_id'  => $itemId,
            'customer_id' => auth()->guard('customer')->user()->id,
        ];

        $checked = $this->wishlistRepository->findWhere([
            'channel_id'  => core()->getCurrentChannel()->id,
            'product_id'  => $itemId,
            'customer_id' => auth()->guard('customer')->user()->id,
        ]);

        //accidental case if some one adds id of the product in the anchor tag amd gives id of a variant.
        if ($product->parent_id != null) {
            $product = $this->productRepository->findOneByField('id', $product->parent_id);
            $data['product_id'] = $product->id;
        }

        if ($checked->isEmpty()) {
            if ($this->wishlistRepository->create($data)) {
                session()->flash('success', trans('customer::app.wishlist.success'));

                return redirect()->back();
            } else {
                session()->flash('error', trans('customer::app.wishlist.failure'));

                return redirect()->back();
            }
        } else {
            $this->wishlistRepository->findOneWhere([
                'product_id' => $data['product_id']
            ])->delete();

            session()->flash('success', trans('customer::app.wishlist.removed'));

            return redirect()->back();
        }
    }

    /**
     * Function to remove item to the wishlist.
     *
     * @param  int  $itemId
     * @return \Illuminate\Http\Response
     */
    public function remove($itemId)
    {
        $customerId = isset(auth()->guard('customer')->user()->id) ? auth()->guard('customer')->user()->id : "1";
		
		if(!is_numeric($itemId)) {
            $itemIds = base64_decode($itemId);
			$expItemIds = explode("~", $itemIds);
			$a=0;
			
			foreach ($expItemIds as $id) {
				
				$productExist = $this->wishlistRepository->findOneByField('id', $id);
				//echo "<pre>";print_r($productExist);die;
				
				if(isset($productExist->id)) {
					if($customerId == 1) {
						$this->wishlistRepository->delete($id);
						$a++;
						if($a == count($expItemIds)) {
							session()->flash('success', trans('customer::app.wishlist.removed'));
						}
					} else {
						$customerWishlistItems = auth()->guard('customer')->user()->wishlist_items;
						
						//Status update//
						DB::table('wishlist_users')->where('customer_id',$customerId)->update(array('mail_alert' => 0));
						//Status update//
				
						foreach ($customerWishlistItems as $customerWishlistItem) {
							if ($id == $customerWishlistItem->id) {
								$this->wishlistRepository->delete($id);
								$a++;
								if($a == count($expItemIds)) {
									session()->flash('success', trans('customer::app.wishlist.removed'));
									return redirect()->back();
								}
							}
						}
						
						session()->flash('error', trans('customer::app.wishlist.remove-fail'));
					}
				} else {
					session()->flash('error', trans('customer::app.wishlist.check-list'));
					return redirect()->back();
				}
			}
		} else {
			$productExist = $this->wishlistRepository->findOneByField('id', $itemId);
			//echo "<pre>";print_r($productExist);die;
			
			if(isset($productExist->id)) {
				if($customerId == 1) {
					$this->wishlistRepository->delete($itemId);
			
					session()->flash('success', trans('customer::app.wishlist.removed'));
					
				} else {
					$customerWishlistItems = auth()->guard('customer')->user()->wishlist_items;
			
			        //Status update//
					DB::table('wishlist_users')->where('customer_id',$customerId)->update(array('mail_alert' => 0));
					//Status update//
			
					foreach ($customerWishlistItems as $customerWishlistItem) {
						if ($itemId == $customerWishlistItem->id) {
							$this->wishlistRepository->delete($itemId);
			
							session()->flash('success', trans('customer::app.wishlist.removed'));
			
							return redirect()->back();
						}
					}
			
					session()->flash('error', trans('customer::app.wishlist.remove-fail'));
				}
			} else {
					session()->flash('error', trans('customer::app.wishlist.check-list'));
					return redirect()->back();
			}
		}

        return redirect()->back();
    }

    /**
     * Function to move item from wishlist to cart.
     *
     * @param  int  $itemId
     * @return \Illuminate\Http\Response
     */
    public function move($itemId)
    {
        $customerId = isset(auth()->guard('customer')->user()->id) ? auth()->guard('customer')->user()->id : "1";
		
		if(!is_numeric($itemId)) {
            $itemIds = base64_decode($itemId);
			$expItemIds = explode("~", $itemIds);
			$a=0;
			$item_options="";
			foreach ($expItemIds as $id) {
				//$productExist = $this->wishlistRepository->findOneByField('product_id', $id);
				$productExist = $this->wishlistRepository->getByorDataId($id);
				//echo "<pre>";print_r($productExist);die;
				foreach($productExist as $proVal) {
					$item_options = $proVal->item_options;
					$rowId = $proVal->id;
				}
				
				if(!empty($rowId)) {
					if($customerId == 1) {
						$wish = $this->getWishUserCookie();
						foreach($wish as $userId) {
							$getUserId = $userId->id;
						}
						
						$wishlistItem = $this->wishlistRepository->findOneWhere([
							'product_id'  => $id,
							'guest_user_id' => $getUserId,
							'customer_id' => $customerId,
							'item_options' => $item_options,
							'id' => $rowId,
						]);
								
					} else {
						$wishlistItem = $this->wishlistRepository->findOneWhere([
							'product_id'  => $id,
							'customer_id' => $customerId,
							'item_options' => $item_options,
							'id' => $rowId,
						]);
					}
					
					if (! $wishlistItem) {
						abort(404);
					}
		
					try {
						$result = Cart::moveToCart($wishlistItem);
			
						if ($result) {
							$a++;
							if($a == count($expItemIds)) {
								session()->flash('success', trans('shop::app.customer.account.wishlist.moved'));
								return redirect()->back();
							}
						} else {
							session()->flash('info', trans('shop::app.checkout.cart.integrity.missing_options'));
							return redirect()->route('shop.productOrCategory.index', $wishlistItem->product->url_key);
						}
						
					} catch (\Exception $e) {
						report($e);
						session()->flash('warning', $e->getMessage());
						return redirect()->route('shop.productOrCategory.index',  $wishlistItem->product->url_key);
					}
				} else {
					session()->flash('error', trans('customer::app.wishlist.check-list'));
					return redirect()->back();
				}
			}
			
        } else {
			
			//$productExist = $this->wishlistRepository->findOneByField('product_id', $itemId);
			$productExist = $this->wishlistRepository->getProductDataId($itemId);
			//echo "<pre>";print_r($productExist);die;
			foreach($productExist as $proVal) {
				$rowId = $proVal->id;
			}
			
			if(!empty($rowId)) {
				if($customerId == 1) {
					$wish = $this->getWishUserCookie();
					foreach($wish as $userId) {
							$getUserId = $userId->id;
					}
					
					$wishlistItem = $this->wishlistRepository->findOneWhere([
						'product_id'  => $itemId,
						'guest_user_id' => $getUserId,
						'customer_id' => $customerId,
						'id' => $rowId,
					]);
							
				} else {
					$wishlistItem = $this->wishlistRepository->findOneWhere([
						'product_id'  => $itemId,
						'customer_id' => $customerId,
						'id' => $rowId,
					]);
				}
				
				if (! $wishlistItem) {
					abort(404);
				}
		
				try {
					$result = Cart::moveToCart($wishlistItem);
		
					if ($result) {
						session()->flash('success', trans('shop::app.customer.account.wishlist.moved'));
					} else {
						session()->flash('info', trans('shop::app.checkout.cart.integrity.missing_options'));
		
						return redirect()->route('shop.productOrCategory.index', $wishlistItem->product->url_key);
					}
		
					return redirect()->back();
				} catch (\Exception $e) {
					report($e);
		
					session()->flash('warning', $e->getMessage());
		
					return redirect()->route('shop.productOrCategory.index',  $wishlistItem->product->url_key);
				}
			} else {
					session()->flash('error', trans('customer::app.wishlist.check-list'));
					return redirect()->back();
			}
	    }		
    }
	
	/**
     * Function to add to cart items in the customer's wishlist
     *
     * @return \Illuminate\Http\Response
     */
	public function addtocart(Request $request)
	{
		$data = request()->input();
		//echo "<pre>";print_r($data);die;
		$finalWishlist = array();
		
		$customerId = isset(auth()->guard('customer')->user()->id) ? auth()->guard('customer')->user()->id : "1";
		
		$expItemIds = explode("~",$data['product_id']);
		$ring_size_val = $data['ring_size_val'];
				
		if(count($expItemIds)>1) {
			$a=0;
			$item_options="";
			foreach ($expItemIds as $id) {
				//$productExist = $this->wishlistRepository->findOneByField('product_id', $id);
				$productExist = $this->wishlistRepository->getByorDataId($id);
				//echo "<pre>";print_r($productExist);die;
				foreach($productExist as $proVal) {
					$item_options = $proVal->item_options;
					$rowId = $proVal->id;
				}
				if(!empty($rowId)) {
					if($customerId == 1) {
						$wish = $this->getWishUserCookie();
						foreach($wish as $userId) {
							$getUserId = $userId->id;
						}
						
						$wishlistItem = $this->wishlistRepository->findOneWhere([
							'product_id'  => $id,
							'guest_user_id' => $getUserId,
							'customer_id' => $customerId,
							'item_options' => $item_options,
							'id' => $rowId,
						]);
								
					} else {
						$wishlistItem = $this->wishlistRepository->findOneWhere([
							'product_id'  => $id,
							'customer_id' => $customerId,
							'item_options' => $item_options,
							'id' => $rowId,
						]);
					}
					
					if (! $wishlistItem) {
						abort(404);
					}
			
					try {
						if(isset($wishlistItem['additional']['selected_configurable_option'])){
							$arrChange = array('ring_size_val' => $ring_size_val,);
							$wishlistItem['additional'] = array_replace($wishlistItem['additional'],$arrChange);
							//echo "<pre>";print_r($wishlistItem);die;
						}
						$result = Cart::moveToCart($wishlistItem);
			
						if ($result) {
							$a++;
							if($a == count($expItemIds)) {
								session()->flash('success', trans('shop::app.customer.account.wishlist.moved'));
								return redirect()->back();
							}
						} else {
							session()->flash('info', trans('shop::app.checkout.cart.integrity.missing_options'));
							return redirect()->route('shop.productOrCategory.index', $wishlistItem->product->url_key);
						}
						
					} catch (\Exception $e) {
						report($e);
						session()->flash('warning', $e->getMessage());
						return redirect()->route('shop.productOrCategory.index',  $wishlistItem->product->url_key);
					}
					
				} else {
					session()->flash('error', trans('customer::app.wishlist.check-list'));
					return redirect()->back();
				}
			}
			
        } else if(count($expItemIds) == 1){
			
			//$productExist = $this->wishlistRepository->findOneByField('product_id', $expItemIds[0]);
			$productExist = $this->wishlistRepository->getProductDataId($expItemIds[0]);
			//echo "<pre>";print_r($productExist);die;
			foreach($productExist as $proVal) {
				$rowId = $proVal->id;
			}
			
			if(!empty($rowId)) {
				if($customerId == 1) {
					$wish = $this->getWishUserCookie();
					foreach($wish as $userId) {
							$getUserId = $userId->id;
					}
					
					$wishlistItem = $this->wishlistRepository->findOneWhere([
						'product_id'  => $expItemIds[0],
						'guest_user_id' => $getUserId,
						'customer_id' => $customerId,
						'id' => $rowId,
					]);
							
				} else {
					$wishlistItem = $this->wishlistRepository->findOneWhere([
						'product_id'  => $expItemIds[0],
						'customer_id' => $customerId,
						'id' => $rowId,
					]);
				}
				
				if (! $wishlistItem) {
					abort(404);
				}
		
				try {
					if(isset($wishlistItem['additional']['selected_configurable_option'])){
						$arrChange = array('ring_size_val' => $ring_size_val,);
						$wishlistItem['additional'] = array_replace($wishlistItem['additional'],$arrChange);
						//echo "<pre>";print_r($wishlistItem);die;
					}
					$result = Cart::moveToCart($wishlistItem);
		
					if ($result) {
						session()->flash('success', trans('shop::app.customer.account.wishlist.moved'));
					} else {
						session()->flash('info', trans('shop::app.checkout.cart.integrity.missing_options'));
		
						return redirect()->route('shop.productOrCategory.index', $wishlistItem->product->url_key);
					}
		
					return redirect()->back();
				} catch (\Exception $e) {
					report($e);
		
					session()->flash('warning', $e->getMessage());
		
					return redirect()->route('shop.productOrCategory.index',  $wishlistItem->product->url_key);
				}
				
			} else {
					session()->flash('error', trans('customer::app.wishlist.check-list'));
					return redirect()->back();
			}
	    }
	}

    /**
     * Function to remove all of the items items in the customer's wishlist
     *
     * @return \Illuminate\Http\Response
     */
    public function removeAll()
    {
        $wishlistItems = auth()->guard('customer')->user()->wishlist_items;

        if ($wishlistItems->count() > 0) {
            foreach ($wishlistItems as $wishlistItem) {
                $this->wishlistRepository->delete($wishlistItem->id);
            }
        }

        session()->flash('success', trans('customer::app.wishlist.remove-all-success'));

        return redirect()->back();
    }
	
	/**
     * Remove Wishlist Session
     */
    public function removeWishlistSession($wishlist = null)
    {
        if (! $wishlist) {
            return $wishlist;
        }
		
		if (session()->has('wishlist')) {
			session()->forget('wishlist');
		}

        return $wishlist;
    }
	
	/**
     * Save Wishlist
     */
    /*public function putWishlist($wish)
    {
        if (! $this->getCurrentCustomer()->check()) {
            session()->put('wishlist', $wish);
        }
    }*/
	
	public function putWishlist($wish)
	{  
	    if (! $this->getCurrentCustomer()->check()) {
            setcookie("userwishlistid", $wish, time() + (86400 * 30), "/");
			return 1;
        }
    }
	
	/**
     * Returns Wishlist
     *
     * @return \Webkul\Customer\Contracts\Wishlist|null
     */
    /*public function getWishUser(): ?\Webkul\Customer\Contracts\Wishlist
    {
        $wishlist = null;
        if ($this->getCurrentCustomer()->check()) {		
            $wishlist = $this->wishlistRepository->getUserCheckData(['customer_id' => $this->getCurrentCustomer()->user()->id, 'is_active' => 1]);

        } elseif (session()->has('wishlist')) {
            $wishlist = $this->wishlistRepository->getGuestCheckData(session()->get('wishlist'));
        }

        //$this->removeWishlistItems($wishlist);

        return $wishlist;
    }*/
	
	public function getWishUserCookie()
    {
		$wishlist = null;
		if ($this->getCurrentCustomer()->check()) {		
            $wishlist = $this->wishlistRepository->getUserCheckData(['customer_id' => $this->getCurrentCustomer()->user()->id, 'is_active' => 1]);

        } elseif (isset($_COOKIE["userwishlistid"])) {
            $wishlist = $this->wishlistRepository->getGuestCheckData($_COOKIE["userwishlistid"]);
        }
		return $wishlist;
    }
	
	/**
     * Return current logged in customer
     *
     * @return \Webkul\Customer\Contracts\Customer|bool
     */
    public function getCurrentCustomer()
    {
        $guard = request()->has('token') ? 'api' : 'customer';

        return auth()->guard($guard);
    }
	
	/**
     * Create new Wishlist instance.
     */
    public function createWishUser()
    {
        $wishData = [
            'channel_id'	=> core()->getCurrentChannel()->id,
            'items_count'	=> 1,
        ];
		
		// Fill in the customer data, as far as possible:
        if ($this->getCurrentCustomer()->check()) {
            $wishData['customer_id'] = $this->getCurrentCustomer()->user()->id;
            $wishData['is_guest'] = 0;
            $wishData['customer_first_name'] = $this->getCurrentCustomer()->user()->first_name;
            $wishData['customer_last_name'] = $this->getCurrentCustomer()->user()->last_name;
            $wishData['customer_email'] = $this->getCurrentCustomer()->user()->email;
        } else {
            $wishData['is_guest'] = 1;
        }
		
		$userwishlist = $this->wishlistRepository->createUser($wishData);
		//$wish = $this->WishlistUserRepository->create($wishData);
		
        if (! $userwishlist) {
			session()->flash('error', trans('customer::app.wishlist.failure'));
            return;
        }
		
		foreach($userwishlist as $key=>$val) {
        	$this->putWishlist($val->id);
		}
		
        return $userwishlist;
    }
	
	/**
     * Function to add item to the wishlist.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createNew($itemId)
    {
        $request = request()->all();
        //echo "<pre>";print_r($request);die;
		
		$wish = $this->getWishUserCookie();
		
		if (!$wish || count($wish)<1) {
            $wish = $this->createWishUser();
        }
		//echo "<pre>";print_r($wish);die;

        $product = $this->productRepository->findOneByField('id', $itemId);
		
        if (! $product->status)
            return redirect()->back();
				
		foreach($wish as $userId) {
			$getUserId = $userId->id;
		}
		
		$customerId = isset(auth()->guard('customer')->user()->id) ? auth()->guard('customer')->user()->id : "1";
		
		$category_id = $this->wishlistRepository->getCategoryId($itemId);
		 
		$data = [
            'channel_id'  => core()->getCurrentChannel()->id,
            'product_id'  => $request['selected_configurable_option'],
            'guest_user_id' => $getUserId,
			'customer_id' => $customerId,
            'prod_type_id' => $category_id,
			'additional' => $request,
        ];
		//echo "<pre>";print_r($data);die;

        $checked = $this->wishlistRepository->findWhere([
            'channel_id'  => core()->getCurrentChannel()->id,
            'product_id'  => $request['selected_configurable_option'],
            'guest_user_id' => $getUserId,
        ]);	
        
        //accidental case if some one adds id of the product in the anchor tag amd gives id of a variant.
        if ($product->parent_id != null) {
            $product = $this->productRepository->findOneByField('id', $product->parent_id);
            $data['product_id'] = $product->id;
        }

        if ($checked->isEmpty()) {
            if ($this->wishlistRepository->create($data)) {
                session()->flash('success', trans('customer::app.wishlist.success'));

			if($customerId!=1){
			 DB::table('wishlist_users')->where('customer_id',$customerId)->update(array('mail_alert' => 0));
			}

                return redirect()->back();
            } else {
                session()->flash('error', trans('customer::app.wishlist.failure'));

                return redirect()->back();
            }
        } else {
            $this->wishlistRepository->findOneWhere([
                'product_id' => $data['product_id'],
				'guest_user_id' => $getUserId
            ])->delete();

            session()->flash('success', trans('customer::app.wishlist.removed'));

            return redirect()->back();
        }
        
    }
	
	/**
     * Function to add diamond item to the wishlist.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addDiamond($itemId)
    {
        $request = request()->all();
        //echo "<pre>";print_r($request);die;
		
		$wish = $this->getWishUserCookie();
		
		if (!$wish || count($wish)<1) {
            $wish = $this->createWishUser();
        }
		//echo "<pre>";print_r($wish);die;

        $product = $this->productRepository->findOneByField('id', $itemId);
		
        if (! $product->status)
            return redirect()->back();
				
		foreach($wish as $userId) {
			$getUserId = $userId->id;
		}
		
		$customerId = isset(auth()->guard('customer')->user()->id) ? auth()->guard('customer')->user()->id : "1";
		$category_id = $this->wishlistRepository->getCategoryId($itemId);
		
		$cartData = array(
                    '_token' => $request['_token'],
                    'quantity' => $request['quantity'],
                    'is_byor' => $request['is_byor'],
                    'is_buy_now' => $request['is_buy_now'],
                    'product_id' => $request['product_id'],
                    'prod_type_id' => $category_id
                );
				
		$data = [
            'channel_id'  => core()->getCurrentChannel()->id,
            'product_id'  => $request['product_id'],
            'guest_user_id' => $getUserId,
			'customer_id' => $customerId,
			'prod_type_id' => $category_id,
			'additional' => $cartData,
        ];
		//echo "<pre>";print_r($data);die;

        $checked = $this->wishlistRepository->findWhere([
            'channel_id'  => core()->getCurrentChannel()->id,
            'product_id'  => $request['product_id'],
            'guest_user_id' => $getUserId,
        ]);	
        
        //accidental case if some one adds id of the product in the anchor tag amd gives id of a variant.
        if ($product->parent_id != null) {
            $product = $this->productRepository->findOneByField('id', $product->parent_id);
            $data['product_id'] = $product->id;
        }

        if ($checked->isEmpty()) {
            if ($this->wishlistRepository->create($data)) {
                session()->flash('success', trans('customer::app.wishlist.success'));
				
			if($customerId!=1){
			 DB::table('wishlist_users')->where('customer_id',$customerId)->update(array('mail_alert' => 0));
			}

                return redirect()->back();
            } else {
                session()->flash('error', trans('customer::app.wishlist.failure'));

                return redirect()->back();
            }
        } else {
            $this->wishlistRepository->findOneWhere([
                'product_id' => $data['product_id'],
				'guest_user_id' => $getUserId
            ])->delete();

            session()->flash('success', trans('customer::app.wishlist.removed'));

            return redirect()->back();
        }
        
    }
	
	/**
     * Function for guests user to add the byor product in the cart.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addbyor($data)
    {
        if(isset($_COOKIE['BrkvByorSess'])) {
            $request = request()->all();
			//echo "<pre>";print_r($request);die;
            $byor_data = json_decode($request['byor_data']);
            //echo $byor_data[0]->product_id."--".$byor_data[1]->product_id; exit();
			
			$wish = $this->getWishUserCookie();
		
			if (!$wish || count($wish)<1) {
				$wish = $this->createWishUser();
			}
			//echo "<pre>";print_r($wish);die;
				
			foreach($wish as $userId) {
				$getUserId = $userId->id;
			}
			
			$customerId = isset(auth()->guard('customer')->user()->id) ? auth()->guard('customer')->user()->id : "1";
		
            $settingArr = array();
            $itemId ="";
            $a=0;
            $itemid2del=0;
			$item_options="";
            foreach ($byor_data as $data) {
                $id = $data->product_id;
				if($a==0) {$item_options = $data->product_id;}
				$category_id = $this->wishlistRepository->getCategoryId($id);
                $cartData = array(
                    '_token' => $request['_token'],
                    'quantity' => $request['quantity'],
                    'is_byor' => $request['is_byor'],
                    'is_buy_now' => $request['is_buy_now'],
                    'product_id' => $data->product_id,
                    'prod_type_id' => $data->category_id
                );
				
                if($data->pairtype == "Setting") {
                    $itemid2del = $id;
                    $assets = route('shop.home.index').'/themes/bliss/assets/';
                    $assets1 = route('shop.home.index').'/public/themes/bliss/assets/';
                    $image = str_replace([$assets,$assets1],"",$data->image);
                    $settingArr['selected_configurable_option'] = $data->variation_product_id;
                    $settingArr['default_image'] = $image;
                    $settingArr['metal_type_val'] = $data->metal_type;
                    $settingArr['shape_default'] = $data->ring_shape;
                    $settingArr['ring_size_val'] = ($request['ring_size_val']) ? $request['ring_size_val'] : $data->ring_size;
                    $cartData = array_merge($cartData,$settingArr);
                	
					$data = [
						'channel_id'  => core()->getCurrentChannel()->id,
						'product_id'  => $settingArr['selected_configurable_option'],
						'guest_user_id' => $getUserId,
						'customer_id' => $customerId,
						'item_options' => $item_options,
            			'prod_type_id' => $category_id,
						'additional' => $cartData,
					];
					
					$checked = $this->wishlistRepository->findWhere([
						'channel_id'  => core()->getCurrentChannel()->id,
						'product_id'  => $settingArr['selected_configurable_option'],
						'guest_user_id' => $getUserId,
						'item_options' => $item_options,
					]);
				
				} else {
					
					$data = [
						'channel_id'  => core()->getCurrentChannel()->id,
						'product_id'  => $id,
						'guest_user_id' => $getUserId,
						'customer_id' => $customerId,
						'item_options' => $item_options,
            			'prod_type_id' => $category_id,
						'additional' => $cartData,
					];
					
					$checked = $this->wishlistRepository->findWhere([
						'channel_id'  => core()->getCurrentChannel()->id,
						'product_id'  => $id,
						'guest_user_id' => $getUserId,
						'item_options' => $item_options,
					]);
				}
                										
				if ($checked->isEmpty()) {
					if ($this->wishlistRepository->create($data)) {
						if($a == (count($byor_data)-1)) {
							session()->flash('success', trans('customer::app.wishlist.success'));
							
						if($customerId!=1){
						 DB::table('wishlist_users')->where('customer_id',$customerId)->update(array('mail_alert' => 0));
						}
							
							return redirect()->back();
						}
					} else {
						session()->flash('error', trans('customer::app.wishlist.failure'));
						return redirect()->back();
					}
				} else {
					$this->wishlistRepository->findOneWhere([
						'product_id' => $data['product_id'],
						'guest_user_id' => $getUserId,
						'item_options' => $item_options,
					])->delete();
					
					if($a == (count($byor_data)-1)) {
						session()->flash('success', trans('customer::app.wishlist.removed'));
						return redirect()->back();
					}
				}
                $a++;
            }
            //$this->removeByorData();
			//session()->flash('success', trans('customer::app.wishlist.success'));
            //return redirect()->route('customer.wishlist.index');
			return redirect()->back();
        } else {
            //return redirect()->route('customer.wishlist.index');
			return redirect()->back();
            // return redirect()->route('shop.home.index');
        }
    }
	
	/* remove byor data from temp table */
    public function removeByorData() {
        $session_id = session()->getId();
        $qr_byor = DB::table('byor_temp')->where('session_id', '=', $session_id)->delete();
        setcookie("BrkvByorSess", "", time()-3600, "/");
        setcookie("BrkvnextByorPage", "", time()-3600, "/");
        return 1;
    }
	
	/**
     * This function handles when guest has some of wishlist products and then logs in.
     *
     * @return void
     */
    public function mergeCart(): void
    {
		if (isset($_COOKIE["userwishlistid"])) {
            $wishlist = $this->wishlistRepository->getUserCheckData([
                'customer_id' => $this->getCurrentCustomer()->user()->id,
                'is_active'   => 1,
            ]);
			
            $guestId = $_COOKIE["userwishlistid"];
			
            //when the logged in customer is not having any of the cart instance previously and are active.
            if (count($wishlist)<1) {
                $this->wishlistRepository->updateUser([
                    'customer_id'         => $this->getCurrentCustomer()->user()->id,
                    'is_guest'            => 0,
                    'customer_first_name' => $this->getCurrentCustomer()->user()->first_name,
                    'customer_last_name'  => $this->getCurrentCustomer()->user()->last_name,
                    'customer_email'      => $this->getCurrentCustomer()->user()->email,
                ], $guestId);

                unset($_COOKIE['userwishlistid']); 
				setcookie('userwishlistid', null, -1, '/'); 
            }
						
			$guestData = $this->wishlistRepository->findWhere([
				'channel_id'  => core()->getCurrentChannel()->id,
				'customer_id'  => 1,
				'guest_user_id' => $guestId,
			]);	
			
			//echo "<pre>";print_r($guestData);die;
			
			foreach($wishlist as $user) {
				$userId = $user->id;
				$this->wishlistRepository->wishlistGuestDelete($guestId);
			}
			
			$wishlist_id = isset($userId) ? $userId : $guestId;

            foreach ($guestData as $guestCartItem) {
				
				$this->wishlistRepository->update([
                    'guest_user_id'         => $wishlist_id,
                    'customer_id' 			=> $this->getCurrentCustomer()->user()->id,
                ], $guestCartItem->id);
            }
			
			$TotalRecd = $this->wishlistRepository->findWhere([
				'channel_id'  => core()->getCurrentChannel()->id,
				'guest_user_id' => $wishlist_id,
			]);	
			
			$this->wishlistRepository->updateUserItems([
                    'items_count'         => count($TotalRecd),
                    'items_qty' 		  => count($TotalRecd),
                ], $wishlist_id);
				
			unset($_COOKIE['userwishlistid']); 
			setcookie('userwishlistid', null, -1, '/'); 
        }
    }
	
	public function addListItem($itemId)
    {

		if(isset($_REQUEST['variation_id'])) {
				$variation_id = explode("-",$_REQUEST['variation_id']);
				$configurable_option = $variation_id[1];
				$product_id = $variation_id[0];
		} else {
				$configurable_option = $itemId+1;
				$product_id = $itemId;
		}

		$defaultImg = (isset($_REQUEST['defaultImg'])) ? $_REQUEST['defaultImg'] : '';
		$get_image = (isset($_REQUEST['image'])) ? $_REQUEST['image'] : $defaultImg;

		$default_image = explode("assets/",$get_image);
		$title = (isset($_REQUEST['title'])) ? $_REQUEST['title'] : "";
        $category_id = $this->wishlistRepository->getCategoryId($itemId);
		$default_image[1] = isset($default_image[1]) ? $default_image[1] : "";

		$request = array(
						"product_id" => $product_id,
						"is_buy_now" => "0",
						"quantity" => "1",
						"selected_configurable_option" => $configurable_option,
						"default_image" => $default_image[1],
						"metal_type_val" => $title,
						"shape_default" => "",
						"prod_type_id" => $category_id,
						"ring_size_val" => "",
						"is_byor" => "0"
						);

		$wish = $this->getWishUserCookie();

		if (!$wish || count($wish)<1) {
            $wish = $this->createWishUser();
        }
		//echo "<pre>";print_r($wish);die;

        $product = $this->productRepository->findOneByField('id', $itemId);

        if (! $product->status)
            return redirect()->back();

		foreach($wish as $userId) {
			$getUserId = $userId->id;
		}

		$customerId = isset(auth()->guard('customer')->user()->id) ? auth()->guard('customer')->user()->id : "1";

		$data = [
            'channel_id'  => core()->getCurrentChannel()->id,
            'product_id'  => $configurable_option,
            'guest_user_id' => $getUserId,
			'customer_id' => $customerId,
            'prod_type_id' => $category_id,
			'additional' => $request,
        ];
		//echo "<pre>";print_r($data);die;

        $checked = $this->wishlistRepository->findWhere([
            'channel_id'  => core()->getCurrentChannel()->id,
            'product_id'  => $configurable_option,
            'guest_user_id' => $getUserId,
        ]);	

        //accidental case if some one adds id of the product in the anchor tag amd gives id of a variant.
        if ($product->parent_id != null) {
            $product = $this->productRepository->findOneByField('id', $product->parent_id);
            $data['product_id'] = $product->id;
        }

        if ($checked->isEmpty()) {
            if ($this->wishlistRepository->create($data)) {
                //session()->flash('success', trans('customer::app.wishlist.success'));
				
			if($customerId!=1){
			 DB::table('wishlist_users')->where('customer_id',$customerId)->update(array('mail_alert' => 0));
			}
				
                return 1;
            } else {
                //session()->flash('error', trans('customer::app.wishlist.failure'));
                return 0;
            }
        } else {
            $this->wishlistRepository->findOneWhere([
                'product_id' => $data['product_id'],
				'guest_user_id' => $getUserId
            ])->delete();

            //session()->flash('success', trans('customer::app.wishlist.removed'));
            return 2;
        }

    }
	
	public function WishListMailSend()
    {
		$users=DB::table('wishlist_users')->where('is_active',1)->where('is_guest',0)->where('items_qty','>',0)->where('mail_alert',0)->get();

        if($users){
			foreach($users as $user){

				$id=$user->customer_id;
			
				$results = [];$total=0;
				$results['engagement'] = $this->wishlistRepository->getCustomerWhishlist('19',$id);
				$results['wedding'] = $this->wishlistRepository->getCustomerWhishlist('17',$id);
				$results['jewelry'] = $this->wishlistRepository->getCustomerWhishlist('13,16,18',$id);
				$results['diamond'] = $this->wishlistRepository->getCustomerWhishlist('2',$id);

				if(count($results['engagement'])>0 || count($results['wedding'])>0 || count($results['jewelry'])>0 || count($results['diamond'])>0)
				{
					$customerDetails = $this->wishlistRepository->getCustomerDetails($id);
					$total = count($results['engagement']) + count($results['wedding']) + count($results['jewelry']) + count($results['diamond']);
					if($total>0) {
						try {
						Mail::queue(new WishlistEmail($results,$user));
						BasecampMail::WishlistMail($results, $customerDetails, $total);
						echo "Mail Sent <br>";
						}catch (Exception $e) {
                        echo "Mail didn't send <br>";  
						}
						
						DB::table('wishlist_users')->where('customer_id',$id)->update(array('mail_alert' => 1));
					}
				}
			
			}
		}		
	}
}
