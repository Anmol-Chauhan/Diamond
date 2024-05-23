<?php

namespace Webkul\Customer\Repositories;

use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\DB;

class CustomerRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */

    function model()
    {
        return 'Webkul\Customer\Contracts\Customer';
    }

    /**
     * Check if customer has order pending or processing.
     *
     * @param Webkul\Customer\Models\Customer
     * @return boolean
     */
    public function checkIfCustomerHasOrderPendingOrProcessing($customer)
    {
        return $customer->all_orders->pluck('status')->contains(function ($val) {
            return $val === 'pending' || $val === 'processing';
        });
    }

    /**
     * Check if bulk customers, if they have order pending or processing.
     *
     * @param array
     * @return boolean
     */
    public function checkBulkCustomerIfTheyHaveOrderPendingOrProcessing($customerIds)
    {
        foreach ($customerIds as $customerId) {
            $customer = $this->findorFail($customerId);

            if ($this->checkIfCustomerHasOrderPendingOrProcessing($customer)) {
                return true;
            }
        }

        return false;
    }
	
	public function getlatestAddress($id=0) {
		$latestAddress = DB::select('select * from `addresses` where `customer_id` = ? AND (`address_type` = "order_billing" OR `address_type` = "cart_billing") ORDER BY updated_at DESC LIMIT 1', [$id]);
        
		return reset($latestAddress);
		
	}
	
	public function getlatestShippingAddress($id=0) {
		$latestAddress = DB::select('select * from `addresses` where `customer_id` = ? AND `address_type` = "cart_shipping" ORDER BY updated_at DESC LIMIT 1', [$id]);
        
		return reset($latestAddress);
		
	}
}