<?php
//app/Helpers/Envato/User.php
//namespace App\Helpers\Envato;
 
use Illuminate\Support\Facades\DB;
 
if(!function_exists('getMaxMinDiamondPrice')){
	function getMaxMinDiamondPrice(){
		$minmaxprice = DB::table('product_flat')->select(\DB::raw("MIN(price) AS StartFrom, MAX(price) AS EndTo"))->whereNotNull('product_flat.url_key')->where('product_flat.status', 1)->where('product_flat.visible_individually', 1)->first();
        return (!empty($minmaxprice) ? $minmaxprice : ''); 
		
	}
}
​
if(!function_exists('getMaxMinDiamondLwrario')){
	function getMaxMinDiamondLwrario(){
		$minmaxlwratio = DB::table('custom_kdmdiamonds')->select(\DB::raw("MIN(LWRatio) AS StartFrom, MAX(LWRatio) AS EndTo"))->where('status', 1)->first();
        return (!empty($minmaxlwratio) ? $minmaxlwratio : ''); 
		
	}
}
​
if(!function_exists('getMaxMinDiamondCarat')){
	function getMaxMinDiamondCarat(){
		$minmaxcarat = DB::table('custom_kdmdiamonds')->select(\DB::raw("MIN(SizeCt) AS StartFrom, MAX(SizeCt) AS EndTo"))->where('status', 1)->first();
        return (!empty($minmaxcarat) ? $minmaxcarat : ''); 
		
	}
}