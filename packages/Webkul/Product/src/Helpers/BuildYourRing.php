<?php

namespace Webkul\Product\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BuildYourRing extends AbstractProduct
{
    /*
     * get the BYOR Data
     */
    public function getByorProduct($type) {
        $byorData = array();
        $session_id = session()->getId();
        $products = DB::select('select * from `byor_temp` where `session_id` = ? order by step asc', [$session_id]);
        $totalProduct = count($products);
        if($totalProduct == 2){
            $a = 0;
            foreach ($products as $product) {
                $product_id = $product->product_id;
                $products = $this->getProductDetails($product_id);
                $pairtype = $product->pairtype;
                $image    = $product->image;
                $title    = $products->name;
                $prodUrl  = $products->url_key;
                $class = ($a == 0) ? "second" : "third";
                if($pairtype == "Diamond") {
                    $price  = $products->price;
                    $catUrl = url('/natural-diamonds');
                } else {
                    $variation_product_id = $product->variation_product_id;
                    $variationproducts = $this->getProductDetails($variation_product_id);
                    $DiscountedProductPrice = $this->getdiscountedamount($variation_product_id);
                    $price  = $variationproducts->price;
					if($DiscountedProductPrice!=0){$price  = $DiscountedProductPrice;}
                    $catUrl = url('/search-engagement-rings');
                }
                $byorData[$a] = array(
                    "class" => $class,
                    "step"  => ucfirst($pairtype),
                    "next"  => $product->next_step,
                    "title" => $title,
                    "price" => number_format($price,2),
                    "view"  => url($prodUrl),
                    "change"=> $catUrl,
                    "image" => $image
                );
                $a++;
            }
        } else if($totalProduct == 1) {
            $a=0;
            $byortype = "";
            foreach ($products as $product) {
                $product_id = $product->product_id;
                $products = $this->getProductDetails($product_id);
                $pairtype = $product->pairtype;
                $image    = $product->image;
                $title    = $products->name;
                $prodUrl  = $products->url_key;
                $byortype = $pairtype;
                if($pairtype == "Diamond") {
                    $price  = $products->price;
                    $catUrl = url('/natural-diamonds');
                } else {
                    $variation_product_id = $product->variation_product_id;
					$variationproducts = $this->getProductDetails($variation_product_id);
                    $DiscountedProductPrice = $this->getdiscountedamount($variation_product_id);
                    $price  = $variationproducts->price;
					if($DiscountedProductPrice!=0){$price  = $DiscountedProductPrice;}
                    $catUrl = url('/search-engagement-rings');
                }
                $byorData[$a] = array(
                    "class" => "second",
                    "step"  => ucfirst($pairtype),
                    "next"  => $product->next_step,
                    "title" => $title,
                    "price" => number_format($price,2),
                    "view"  => url($prodUrl),
                    "change"=> $catUrl,
                    "image" => $image
                );
                $a++;
            }
            if($byortype == "Diamond") {
                $byorData[1] = $this->settingDefault('third','step-setting-white.png');
            } else {
                $byorData[1] = $this->diamondDefault('third','step-diamond-white.png');
            }
        } else {
            if($type=="Diamond") {
                $byorData[0] = $this->diamondDefault('second','step-diamond-white.png');
                $byorData[1] = $this->settingDefault('third','step-setting.png');
            } else {
                $byorData[0] = $this->settingDefault('second','step-setting-white.png');
                $byorData[1] = $this->diamondDefault('third','step-diamond.png');
            }
        }
        $byorData[2] = array(
            "class" => "last",
            "step"  => "Review",
            "title" => "Review Your Ring",
            "image" => asset('themes/bliss/assets/images/step-review.png')
        );
        return $byorData;
    }

    /*
     * get the product info by id from product_flat table
     */
    public function getProductDetails($product_id) {
        $products = DB::select('select * from `product_flat` where `product_id` = ?', [$product_id]);
        return reset($products);
    }

    public function getStepsDataByType($type) {
        if($type == "Diamond") {
            $stepData = array(
                "step"  => "Diamond",
                "title" => "Choose Diamond",
                "image" => asset('themes/bliss/assets/images/diamond-hd-png.png')
            );
        } else {
            $stepData = array(
                "step"  => "Setting",
                "title" => "Choose Setting",
                "image" => asset('themes/bliss/assets/images/slider-img2.png')
            );
        }
    }

    public function diamondDefault($a,$image) {
        $data = array(
            "class" => $a,
            "step"  => "Diamond",
            "title" => "Choose Diamond",
            "image" => asset('themes/bliss/assets/images/'.$image)
        );
        return $data;
    }

    public function settingDefault($a,$image) {
        $data = array(
            "class" => $a,
            "step"  => "Setting",
            "title" => "Choose Setting",
            "image" => asset('themes/bliss/assets/images/'.$image)
        );
        return $data;
    }

    public function UpdateSession() {
        $session_id = session()->getId();
        if(isset($_COOKIE['BrkvByorSess']) && isset($_COOKIE['BrkvnextByorPage'])) {
            $prevSess_id = $_COOKIE['BrkvByorSess'];
            $nextbyorpage = $_COOKIE['BrkvnextByorPage'];
            if($prevSess_id != $session_id) {
                DB::table('byor_temp')->where('session_id', $prevSess_id)->update(['session_id' => $session_id]);
                setcookie("BrkvByorSess", $session_id, time() + (86400 * 30), "/");
                return $nextbyorpage;
            }
            return $nextbyorpage;
        }
        return ;
    }

	/* get custom product discount */
    public function getdiscountedamount($productId) {

		$query = DB::table('catalog_rule_product_prices')
		        ->leftJoin('catalog_rule_products', 'catalog_rule_products.product_id', '=', 'catalog_rule_product_prices.product_id')  
                ->select('catalog_rule_product_prices.*')
				->where('catalog_rule_product_prices.product_id', $productId)
				->where('catalog_rule_product_prices.starts_from', '<=' , date('Y-m-d H:i:s'))
				->where('catalog_rule_product_prices.ends_till', '>=' , date('Y-m-d H:i:s'))
				->first();
			
        $price = 0;			
		if(!empty($query)){ $price = $query->price; }		
		
        return $price;
	}	
		
	/* remove diamond byor data from temp table */
    public function DeleteByorData() {
        $session_id = session()->getId();
        $qr_byor = DB::table('byor_temp')->where('session_id',$session_id)->where('pairtype','Diamond')->delete();
    }
}
