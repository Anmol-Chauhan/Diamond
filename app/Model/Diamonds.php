<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB; // added for get the diamonds data

class Diamonds extends Model
{
	protected $table ="custom_kdmdiamonds";

	public static function Dimaonddata($product_id) {
		// $DiamondData = DB::select('select * from `custom_kdmdiamonds` where `posts_id` = ?', [$product_id]);
		$DiamondData = "Diamond Data";
		return $DiamondData;
	}
}