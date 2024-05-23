<?php

namespace Webkul\Customer\Helpers;

class FindCountryHelper
{
    /**
     * Returns wishlist products for current customer.
     *
     * @param  \Webkul\Product\Contracts\Product  $product
     * @return boolean
     */
    public function get_country()
    {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} else if (isset($_SERVER['HTTP_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} else if (isset($_SERVER['REMOTE_ADDR'])) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {
			$ipaddress = 'UNKNOWN';
		}
		
		$json     = file_get_contents("http://ipinfo.io/$ipaddress/geo");
		$json     = json_decode($json, true);
		if(!empty($json['country'])){
		return $json['country'];
		}
		
		return "IN";
    }
}