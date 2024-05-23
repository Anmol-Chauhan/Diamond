<?php

namespace Webkul\Customer\Repositories;

use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class WishlistRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */

    function model()
    {
        return 'Webkul\Customer\Contracts\Wishlist';
    }

    /**
     * @param  array  $data
     * @return \Webkul\Customer\Contracts\Wishlist
     */
    public function create(array $data)
    {
        $wishlist = $this->model->create($data);

        return $wishlist;
    }

    /**
     * @param  array  $data
     * @param  int  $id
     * @param  string  $attribute
     * @return \Webkul\Customer\Contracts\Wishlist
     */
    public function update(array $data, $id, $attribute = "id")
    {
        $wishlist = $this->find($id);

        $wishlist->update($data);

        return $wishlist;
    }

    /**
     * To retrieve products with wishlist for a listing resource.
     *
     * @param  int  $id
     * @return \Webkul\Customer\Contracts\Wishlist
     */
    public function getItemsWithProducts($id)
    {
        return $this->model->find($id)->item_wishlist;
    }

    /**
     * get customer wishlist Items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCustomerWhishlist($category,$UserId=null)
    {
		if($UserId!=null){ $id = $UserId; }else{ $id = auth()->guard('customer')->user()->id; }
		
        $query = $this->model;

        if (! core()->getConfigData('catalog.products.homepage.out_of_stock_items')) {
            $query = $this->model
            ->leftJoin('products as ps', 'wishlist.product_id', '=', 'ps.id')
            ->leftJoin('product_inventories as pv', 'ps.id', '=', 'pv.product_id')
			->leftJoin('product_flat as pf', 'ps.parent_id', '=', 'pf.product_id')
			->select('wishlist.id','wishlist.channel_id','wishlist.product_id','wishlist.guest_user_id','wishlist.customer_id','wishlist.created_at','wishlist.updated_at','wishlist.additional','ps.sku','type','ps.parent_id','attribute_family_id','qty','inventory_source_id','vendor_id','pf.name','pf.url_key')
            ->where(function ($qb) {
                $qb
                    ->WhereIn('ps.type', ['configurable', 'grouped', 'downloadable', 'bundle', 'booking'])
                    ->orwhereIn('ps.type', ['simple', 'virtual'])->where('pv.qty' , '>=' , 0);
            });
        }
		
		if($category == 19)
		{			
			return $query->where('channel_id', core()->getCurrentChannel()->id)
                         ->where('customer_id', $id)
                         ->where('prod_type_id', $category)
						 ->orWhere('prod_type_id','=',2)
						 ->whereNotNull('item_options')
						 ->where('customer_id', $id)
                         ->orderBy('id', 'desc')->paginate(100);
		} 
		else if($category == 2)
		{
			return $query->where('channel_id', core()->getCurrentChannel()->id)
                         ->where('customer_id', $id)
                         ->where('prod_type_id', $category)
						 ->whereNull('item_options')
                         ->orderBy('id', 'desc')->paginate(100);
		}
		else if($category == 17)
		{
			return $query->where('channel_id', core()->getCurrentChannel()->id)
                         ->where('customer_id', $id)
                         ->where('prod_type_id', $category)
                         ->orderBy('id', 'desc')->paginate(100);
		} else {
			$cat_flt = explode(',',$category);
			
			return $query->where('channel_id', core()->getCurrentChannel()->id)
                         ->where('customer_id', $id)
                         ->whereIn('prod_type_id', $cat_flt)
                         ->orderBy('id', 'desc')->paginate(100);
		}

        /*return $query->where([
            'channel_id'  => core()->getCurrentChannel()->id,
            'customer_id' => auth()->guard('customer')->user()->id,
        ])->paginate(10);*/
    }
	
	public function getCustomerWhishlistHeader()
    {
        $query = $this->model;

        if (! core()->getConfigData('catalog.products.homepage.out_of_stock_items')) {
            $query = $this->model
            ->leftJoin('products as ps', 'wishlist.product_id', '=', 'ps.id')
            ->leftJoin('product_inventories as pv', 'ps.id', '=', 'pv.product_id')
			->select('wishlist.id','wishlist.channel_id','wishlist.product_id','wishlist.guest_user_id','wishlist.customer_id','wishlist.created_at','wishlist.updated_at','wishlist.additional','sku','type','parent_id','attribute_family_id','qty','inventory_source_id','vendor_id')
            ->where(function ($qb) {
                $qb
                    ->WhereIn('ps.type', ['configurable', 'grouped', 'downloadable', 'bundle', 'booking'])
                    ->orwhereIn('ps.type', ['simple', 'virtual'])->where('pv.qty' , '>=' , 0);
            });
        }
		
		return $query->where('channel_id', core()->getCurrentChannel()->id)
					 ->where('customer_id', auth()->guard('customer')->user()->id)
					 ->orderBy('id', 'desc')->paginate(200);
						 
        /*return $query->where([
            'channel_id'  => core()->getCurrentChannel()->id,
            'customer_id' => auth()->guard('customer')->user()->id,
        ])->paginate(100);*/
    }
	
	/**
     * get customer wishlist Items count.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCustomerWhishlistCount()
    {
        $wishlist_count = DB::table('wishlist')->select(\DB::raw("id,product_id,customer_id,item_options,created_at,additional"))
        ->where('channel_id', core()->getCurrentChannel()->id)
		->where('customer_id', auth()->guard('customer')->user()->id)
		->groupBy('item_options','created_at')
		->get();

        return $wishlist_count;
    }
	
	public function getCustomerWhishlistERCount($category)
    {
        $er_count = DB::table('wishlist')->select(\DB::raw("id,product_id,customer_id,item_options,created_at,additional"))
        ->where('channel_id', core()->getCurrentChannel()->id)
		->where('customer_id', auth()->guard('customer')->user()->id)
		->where('prod_type_id', $category)
		->get();

        return $er_count;
    }
	
	public function getGuestWhishlistCount($getUserId)
    {
        $wishlist_count = DB::table('wishlist')->select(\DB::raw("id,product_id,customer_id,item_options,created_at,additional"))
        ->where('channel_id', core()->getCurrentChannel()->id)
		->where('guest_user_id', $getUserId)
		->where('customer_id', 1)
		->groupBy('item_options','created_at')
		->get();

        return $wishlist_count;
    }
	
	/**
     * get Guest wishlist Items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGuestWhishlist($getUserId,$category)
    {
        $query = $this->model;
		
        if (! core()->getConfigData('catalog.products.homepage.out_of_stock_items')) {
            $query = $this->model
            ->leftJoin('products as ps', 'wishlist.product_id', '=', 'ps.id')
            ->leftJoin('product_inventories as pv', 'ps.id', '=', 'pv.product_id')
			->select('wishlist.id','wishlist.channel_id','wishlist.product_id','wishlist.guest_user_id','wishlist.customer_id','wishlist.created_at','wishlist.updated_at','wishlist.additional','sku','type','parent_id','attribute_family_id','qty','inventory_source_id','vendor_id')
            ->where(function ($qb) {
                $qb
                    ->WhereIn('ps.type', ['configurable', 'grouped', 'downloadable', 'bundle', 'booking'])
                    ->orwhereIn('ps.type', ['simple', 'virtual'])->where('pv.qty' , '>=' , 0);
            });
        }
		
		if($category == 19)
		{			
			return $query->where('channel_id', core()->getCurrentChannel()->id)
                         ->where('guest_user_id', $getUserId)
                         ->where('customer_id', 1)
                         ->where('prod_type_id', $category)
						 ->orWhere('prod_type_id','=',2)
						 ->whereNotNull('item_options')
						 ->where('guest_user_id', $getUserId)
						 ->where('customer_id', 1)
                         ->orderBy('id', 'desc')->paginate(100);
		} 
		else if($category == 2)
		{
			return $query->where('channel_id', core()->getCurrentChannel()->id)
                         ->where('guest_user_id', $getUserId)
                         ->where('prod_type_id', $category)
                         ->where('customer_id', 1)
						 ->whereNull('item_options')
                         ->orderBy('id', 'desc')->paginate(100);
		}
		else if($category == 17)
		{
			return $query->where('channel_id', core()->getCurrentChannel()->id)
                         ->where('guest_user_id', $getUserId)
                         ->where('prod_type_id', $category)
                         ->where('customer_id', 1)
                         ->orderBy('id', 'desc')->paginate(100);
		} else {
			$cat_flt = explode(',',$category);
			
			return $query->where('channel_id', core()->getCurrentChannel()->id)
                         ->where('guest_user_id', $getUserId)
                         ->where('customer_id', 1)
                         ->whereIn('prod_type_id', $cat_flt)
                         ->orderBy('id', 'desc')->paginate(100);
		}
		
        /*return $query->where([
            'channel_id'  => core()->getCurrentChannel()->id,
            'guest_user_id' => $getUserId,
			'prod_type_id' => $category,
			'customer_id' => 1,
        ])->paginate(10);*/
    }
	
	public function getGuestWhishlistERCount($getUserId,$category)
    {
        $er_count = DB::table('wishlist')->select(\DB::raw("id,product_id,customer_id,item_options,created_at,additional"))
        ->where('channel_id', core()->getCurrentChannel()->id)
		->where('guest_user_id', $getUserId)
		->where('prod_type_id', $category)
		->where('customer_id', 1)
		->get();

        return $er_count;
    }	
	
	public function getGuestWhishlistHeader($getUserId)
    {
        $query = $this->model;
		
        if (! core()->getConfigData('catalog.products.homepage.out_of_stock_items')) {
            $query = $this->model
            ->leftJoin('products as ps', 'wishlist.product_id', '=', 'ps.id')
            ->leftJoin('product_inventories as pv', 'ps.id', '=', 'pv.product_id')
			->select('wishlist.id','wishlist.channel_id','wishlist.product_id','wishlist.guest_user_id','wishlist.customer_id','wishlist.created_at','wishlist.updated_at','wishlist.additional','sku','type','parent_id','attribute_family_id','qty','inventory_source_id','vendor_id')
            ->where(function ($qb) {
                $qb
                    ->WhereIn('ps.type', ['configurable', 'grouped', 'downloadable', 'bundle', 'booking'])
                    ->orwhereIn('ps.type', ['simple', 'virtual'])->where('pv.qty' , '>=' , 0);
            });
        }
		
		return $query->where('channel_id', core()->getCurrentChannel()->id)
					 ->where('guest_user_id', $getUserId)
					 ->orderBy('id', 'desc')->paginate(200);
					 
        /*return $query->where([
            'channel_id'  => core()->getCurrentChannel()->id,
            'guest_user_id' => $getUserId,
			'customer_id' => 1,
        ])->paginate(12);*/
    }
	
	public function deleteCustomerWishlist($itemId,$customerId)
    {
        DB::table('wishlist')->where('product_id', $itemId)->where('customer_id', $customerId)->delete();		
    }
	
	public function deleteGuestWishlist($itemId,$wishlistId)
    {
        DB::table('wishlist')->where('product_id', $itemId)->where('guest_user_id', $wishlistId)->delete();		
    }
	
	public function wishlistdelete($id)
    {
        DB::table('wishlist')->where('guest_user_id', $id)->where('customer_id', 1)->delete();		
    }
	
	public function wishlistGuestDelete($id)
    {
        DB::table('wishlist_users')->where('id', $id)->where('is_guest', 1)->delete();		
    }
	
	/**
     * @param  array  $data
     * @return \Webkul\Customer\Contracts\Wishlist
     */
    public function createUser(array $data)
    {
        //$wishlist = DB::table('wishlist_users')->select(\DB::raw("id,customer_email,customer_first_name,customer_last_name,is_guest,customer_id,channel_id"))
        //->orderBy('id', 'desc')->take(1)->get();
		//->where('overall_rating', 5)->where('is_hide', '0')
		
		if(isset($data['is_guest']) && $data['is_guest'] == 0) {
			
			$insert = DB::table('wishlist_users')
						->insertGetId(['customer_email' => $data['customer_email'],'customer_first_name' => $data['customer_first_name'],'customer_last_name' => $data['customer_last_name'],'items_count' => $data['items_count'],'items_qty' => $data['items_count'],'is_guest' => $data['is_guest'],'is_active' => 1,'customer_id' => $data['customer_id'],'channel_id' => $data['channel_id'],'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")]);
						
		} else {
			$insert = DB::table('wishlist_users')
				->insertGetId(['items_count' => $data['items_count'],'items_qty' => $data['items_count'],'is_guest' => $data['is_guest'],'is_active' => 1,'channel_id' => $data['channel_id'],'created_at' => date("Y-m-d H:i:s"),'updated_at' => date("Y-m-d H:i:s")]);	
		}		
		
		$wishlist = DB::table('wishlist_users')->select(\DB::raw("id"))
        ->where('id', $insert)->take(1)->get();			
		//$wishlist = $this->model->create($data);
		//echo "<pre>";print_r($data);die;
        return $wishlist;
    }
	
	public function getUserCheckData(array $data)
    {
        $wishlist = DB::table('wishlist_users')->select(\DB::raw("id,items_count,is_guest,customer_id,channel_id,created_at,updated_at"))
        ->where('customer_id', $data['customer_id'])->where('is_active', $data['is_active'])->take(1)->get();
		
		//echo "<pre>";print_r($data);die;
        return $wishlist;
    }
	
	public function getGuestCheckData($id)
    {
        $wishlist = DB::table('wishlist_users')->select(\DB::raw("id,items_count,is_guest,customer_id,channel_id,created_at,updated_at"))
        ->where('id', $id)->where('is_active', 1)->take(1)->get();
		
		//echo "<pre>";print_r($data);die;
        return $wishlist;
    }
	
	public function getCategoryId($product_id) {
		$getCategoryId = DB::select('select `category_id` from `product_categories` where `product_id` = ?', [$product_id]);
		$category = reset($getCategoryId);
		$category_id = $category->category_id;
		return $category_id;
	}
	
	public function getByorDataId($product_id) {
		$getByorId = DB::select('select * from `wishlist` where `item_options` is not null and `product_id` = ?', [$product_id]);
		return $getByorId;
	}
	
	public function getProductDataId($product_id) {
		$getProductDataId = DB::select('select * from `wishlist` where `item_options` is null and `product_id` = ?', [$product_id]);
		return $getProductDataId;
	}
	
	public function updateUser(array $data, $id)
    {
		$updatewishlist = DB::table('wishlist_users')->where('id', $id)->where('is_active', 1)->update(array('customer_id' => $data['customer_id'], 'is_guest' => $data['is_guest'], 'customer_first_name' => $data['customer_first_name'], 'customer_last_name' => $data['customer_last_name'], 'customer_email' => $data['customer_email']));
		
        return $updatewishlist;
    }
	
	public function updateUserItems(array $data, $id)
    {
		$updateItem = DB::table('wishlist_users')->where('id', $id)->where('is_active', 1)->update(array('items_count' => $data['items_count'], 'items_qty' => $data['items_qty']));
		
        return $updateItem;
    }
	
	public function getCustomerDetails($UserId=null)
    {
		if($UserId!=null){ $id = $UserId; }else{ $id = auth()->guard('customer')->user()->id; }
		
        $er_count = DB::table('customers')->select(\DB::raw("id,first_name,last_name,email"))
		->where('id',$id)
		->get();

        return $er_count;
    }
	
	public function getCategorySlug($product_id) {
		
	    $productData = DB::table("product_categories")->where('product_id',$product_id)->get();
		
		$last_index=count($productData)-1;
	  
		$category_id=$productData[$last_index]->category_id;
		
		return $productData = DB::table("category_translations")->select('slug')->where('category_id',$category_id)->first();
	}
	
	public function getProductDetailsEmail($productId) {
		
		$ProductskuInfo = DB::select('select sku, id, name, url_key, min_price, description,product_id from `product_flat` where `product_flat`.`product_id` = ?', [$productId]);
        return $ProductskuInfo;		
	}
	
	public function wishlistErrorDelete($itemId,$wishlistId)
    {
		if(!empty(auth()->guard('customer')->user()->id)){
		$customerId = auth()->guard('customer')->user()->id;
		DB::table('wishlist')->where('product_id', $itemId)->where('customer_id', $customerId)->delete();
		}else{
        DB::table('wishlist')->where('product_id', $itemId)->where('guest_user_id', $wishlistId)->delete();	
		}			
    }
}