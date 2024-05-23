<?php

namespace Webkul\Customer\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // added for get the diamonds data
use Mail;
use Config;

class BasecampMail
{
    public function senderDetails() {
        $useragent= isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
         if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
        {
            $request_from = 'Mobile';
        }
        else
        {
            $request_from = 'Desktop';
        }
		
		///
		$ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
        else
        $ipaddress = 'UNKNOWN';
	    ////
		
		$user_ip = $ipaddress;
		//$geo = json_decode(file_get_contents('http://ip-api.com/json/'.$user_ip),true);
		$region = "New York";
		$city = "Brooklyn";
		$country = "US";
          return [
            'to_mail'       => 'message-104628164-9d8024795843425f8ae2db4b@basecamp.com',
            'user_agent'    => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "",
            'ip_address'    => $user_ip.'/'.$country.'/'.$region.'/'.$city,
            'refferal_url'  => (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : '',
            'page_url'      => url()->current(),
            'reffered_by'   => 'Direct',
            'request_from'   => $request_from,
        ];
    }

    public static function contactMail($data)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Barkevs Contact' . "\n";
        $sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a') . "\n";
        $sent_to_basecamp .= 'Name: ' . $data['name'] . "\n";
        $sent_to_basecamp .= 'Email: ' . $data['email'] . "\n";
        $sent_to_basecamp .= 'Comment: ' . $data['comments'] . "\n";
        $sent_to_basecamp .= 'Phone: ' . $data['phone'] . "\n";
        $sent_to_basecamp .= 'Page URL: ' . $data['page-url']. "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $data['refferal-url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $data['user-agent'] . "\n";

        if (!empty($sent_to_basecamp))
        {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email, 'Basecamp');
                $message->subject('Thank you for contacting us');
            });
        }
    }

    public static function forgetPassword($data)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Customer Reset Password' . "\n";
        $sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a') . "\n";
        $sent_to_basecamp .= 'Email: ' . $data['email'] . "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

            if (!empty($sent_to_basecamp))
            {
                Mail::raw($sent_to_basecamp, function ($message) use($email){
                    $message->from('info@barkevs.com', 'Barkevs');
                    $message->to($email, 'Basecamp');
                    $message->subject('Customer Reset Password');
                });
            }
    }

    public static function registrationVerification($data)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Barkevs Verification Email' . "\n";
        $sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a') . "\n";
        $sent_to_basecamp .= 'Name: ' . $data['first_name'].' '.$data['last_name']. "\n";
        $sent_to_basecamp .= 'Email: ' . $data['email'] . "\n";
        $sent_to_basecamp .= 'Password: ' . $data['password'] . "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {
        Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email, 'Basecamp');
                $message->subject('Verification Email');
            });
        }
    }

    public static function updatePasswordMail($data)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Password Updated' . "\n";
        $sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a') . "\n";
        $sent_to_basecamp .= 'Name: ' .'Dear '. ucwords($data['first_name']) .' '.ucwords($data['last_name'])."\n".'You are receiving this email because you have updated your password. Thanks!'. "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email,'Basecamp');
                $message->subject('Password Updated');
            });
        }
    }

    public static function customerRegistration($data, $password)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'New Customer Registration' . "\n";
        $sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a') . "\n";
        $sent_to_basecamp .= 'Name: ' .'Dear '. ucwords($data['first_name']) .' '.ucwords($data['last_name'])."\n" .'Your account has been created. Your account details are below: '. "\n";
        $sent_to_basecamp .= 'UserName/Email: ' . $data['email'] . "\n";
        $sent_to_basecamp .= 'Password: ' . $password . "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email,'Basecamp');
                $message->subject('New Customer Registration');
            });
        }
    }

    public static function orderCommentMail($order, $comment, $id)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'New comment added to your order'.' #'.$id. "\n";
        $sent_to_basecamp .= 'Name: ' .'Dear '.ucwords($order->customer_first_name). ' '.ucwords($order->customer_last_name). "\n";
        $sent_to_basecamp .= 'Order Date: ' . date('F d, Y, h:i a', strtotime($order->created_at)) . "\n";

        $sent_to_basecamp .= 'Order: ' . '#'.$id. "\n";
        $sent_to_basecamp .= 'Comment: ' .$comment->comment. "\n";


        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
            $message->from('info@barkevs.com', 'Barkevs');
            $message->to($email,'Basecamp');
            $message->subject('New comment added');
            });
        }
    }

    public static function guestCustomerRegistration($data, $password)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Customer Registered Successfully' . "\n";
        $sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a') . "\n";
        $sent_to_basecamp .= 'Name: ' .'Dear '. ucwords($data['first_name']) .' '.ucwords($data['last_name'])."\n" .'Please find the login details below: '. "\n";
        $sent_to_basecamp .= 'Email: ' . $data['email'] . "\n";
        $sent_to_basecamp .= 'Password: ' . $password . "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email,'Basecamp');
                $message->subject('Customer Registered Successfully');
            });
        }
    }

    public static function NewOrderMail($order, $shipping_address,$billing_address)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'New Order Confirmation' . "\n";
        $sent_to_basecamp .= 'Order Date: ' . date('F d, Y, h:i a', strtotime($order->created_at)) . "\n";
        $sent_to_basecamp .= 'Name: ' . ucwords($order->customer_first_name). ' '.ucwords($order->customer_last_name). "\n";
        $sent_to_basecamp .= 'Email: ' . $order->customer_email. "\n";
        $sent_to_basecamp .= 'Order: ' . '#'.$order->increment_id. "\n";
        $sent_to_basecamp .= 'Total Items: ' .$order->total_item_count. "\n";
        $sent_to_basecamp .= 'Order Status: ' .$order->status. "\n";
        $sent_to_basecamp .= 'Billing Address: ' .ucwords($billing_address->first_name). ' '.ucwords($billing_address->last_name). "\n";
         $sent_to_basecamp .= 'Company: ' .ucwords($billing_address->company_name).' '.$billing_address->address1. ' '.$billing_address->address2.', '.$billing_address->city.', '.$billing_address->state.' '.$billing_address->postcode.', '.$billing_address->country."\n";
        $sent_to_basecamp .= 'Phone: ' .$billing_address->phone. "\n";
		$sent_to_basecamp .= 'Order will be sent to: ' .ucwords($shipping_address->first_name). ' '.ucwords($shipping_address->last_name). "\n";
         $sent_to_basecamp .= 'Company: ' .ucwords($shipping_address->company_name).' '.$shipping_address->address1. ' '.$shipping_address->address2.', '.$shipping_address->city.', '.$shipping_address->state.' '.$shipping_address->postcode.', '.$shipping_address->country."\n";
        $sent_to_basecamp .= 'Phone: ' .$shipping_address->phone. "\n";

       foreach ($order->items as $key => $value) {
        $sent_to_basecamp .= "\n";
		$skuValue = 'SKU: ' .$value->sku. "\n";
		$diamondInfos = $senderData->getDiamondInfo($value->sku);
		
		$diamondDeatails = "";
		$realCost = "";
		if(!empty($diamondInfos)){
			$diamondInfo = $diamondInfos[0];
			$diamondDeatails = " ( #".$diamondInfo->Sku." - ".$diamondInfo->name.", ".$diamondInfo->CertType." ". $diamondInfo->Style." SKU ".$diamondInfo->stockNumber." )";
			$markup_per = round(((($value->price/($diamondInfo->WholesalePrice))-1)*100),1);
			$realCost = "Real Cost: $".number_format($diamondInfo->WholesalePrice,2)."(".$markup_per."%) \n";
			$skuValue = "";
		}
        $sent_to_basecamp .= 'Product: ' .$value->name.$diamondDeatails. "\n";
        $sent_to_basecamp .= $skuValue;
		if(isset($value->additional['ring_size_val']))
			$sent_to_basecamp .= 'Ring Size: '. 'US '.$value->additional['ring_size_val']. "\n";
		if(isset($value->additional['metal_type_val']))
			$sent_to_basecamp .= 'Metal: ' .$value->additional['metal_type_val']. "\n";
        $sent_to_basecamp .= 'Quantity: ' .$value->qty_ordered. "\n";
        $sent_to_basecamp .= 'Product Price: ' .'$'.number_format($value->base_total,2). "\n";
		$sent_to_basecamp .= $realCost;

        }

        $sent_to_basecamp .= "\n";
        $sent_to_basecamp .= 'Sub Total: ' . '$' .number_format($order->sub_total,2). "\n";
        $sent_to_basecamp .= 'Tax: ' . '$' .number_format($order->tax_amount,2). "\n";
        $sent_to_basecamp .= 'Discount:' .' -$'.number_format($order->discount_amount,2). "\n";
        $sent_to_basecamp .= 'Shipping: ' . '$' .number_format($order->shipping_amount,2). "\n";
        $sent_to_basecamp .= 'Total: ' . '$' .number_format($order->grand_total,2) . "\n";
        $sent_to_basecamp .= 'Payment Via: ' .core()->getConfigData('sales.paymentmethods.' . $order->payment->method . '.title'). "\n";
		if(isset($order->order_notes))
			$sent_to_basecamp .= "Order Notes: " . $order->order_notes . "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";
        
		
		//$headers = "MIME-Version: 1.0" . "\r\n";
		//$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

		//$headers .= 'From: <info@barkevs.com>' . "\r\n";

		//mail($email,'New Order Confirmation',$sent_to_basecamp,$headers);
		Mail::raw($sent_to_basecamp, function ($message) use($email){
            $message->from('info@barkevs.com', 'Barkevs');
            $message->to($email,'Basecamp');
            $message->subject('New Order Confirmation');
        });
        
		return true;
    }

    public static function OrderCancelMail($order, $orderId)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Order Cancel Confirmation' . "\n";
        $sent_to_basecamp .= 'Order Date: ' . date('F d, Y, h:i a', strtotime($order->created_at)) . "\n";
        $sent_to_basecamp .= 'Name: ' . ucwords($order->customer_first_name). ' '.ucwords($order->customer_last_name). "\n";
        $sent_to_basecamp .= 'Email: ' . $order->customer_email. "\n";
        $sent_to_basecamp .= 'Order: ' . '#'.$orderId. "\n";
        $sent_to_basecamp .= 'Total Items: ' .$order->total_item_count. "\n";

        foreach ($order->items as $key => $value) {
        $sent_to_basecamp .= "\n";
        $sent_to_basecamp .= 'Product: ' .$value->name. "\n";
        $sent_to_basecamp .= 'SKU: ' .$value->sku. "\n";
		if(isset($value->additional['ring_size_val']))
			$sent_to_basecamp .= 'Ring Size: '. 'US '.$value->additional['ring_size_val']. "\n";
		if(isset($value->additional['metal_type_val']))
			$sent_to_basecamp .= 'Metal: ' .$value->additional['metal_type_val']. "\n";
        $sent_to_basecamp .= 'Quantity: ' .$value->qty_ordered. "\n";
        $sent_to_basecamp .= 'Product Price: ' .'$'.number_format($value->base_total,2). "\n";

        }
        $sent_to_basecamp .= "\n";
        $sent_to_basecamp .= 'Sub Total: ' . '$' .number_format($order->sub_total,2). "\n";
        $sent_to_basecamp .= 'Tax: ' . '$' .number_format($order->tax_amount,2). "\n";
        $sent_to_basecamp .= 'Discount:' .' -$'.number_format($order->discount_amount,2). "\n";
        $sent_to_basecamp .= 'Shipping: ' . '$' .number_format($order->shipping_amount,2). "\n";
        $sent_to_basecamp .= 'Total: ' . '$' .number_format($order->grand_total,2) . "\n";
        $sent_to_basecamp .= 'Payment Via: ' .core()->getConfigData('sales.paymentmethods.' . $order->payment->method . '.title'). "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {  
           Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email,'Basecamp');
                $message->subject('Order Cancel Confirmation');
            });
        }
    }

    public static function CreateInvoiceMail($order, $invoice)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Invoice for your order'. "\n";
        $sent_to_basecamp = 'Your Invoice'.' #'.$invoice->id.' for Order ' .'#'.$order->increment_id. "\n";
        $sent_to_basecamp .= 'Name: ' .'Dear '.ucwords($order->customer_first_name). ' '.ucwords($order->customer_last_name). "\n";
        $sent_to_basecamp .= 'Order Date: ' . date('F d, Y, h:i a', strtotime($order->created_at)) . "\n";

        $sent_to_basecamp .= 'Email: ' . $order->customer_email. "\n";
        $sent_to_basecamp .= 'Order: ' . '#'.$order->increment_id. "\n";
        $sent_to_basecamp .= 'Total Items: ' .$order->total_item_count. "\n";
        $sent_to_basecamp .= 'Shipping Method: ' .$order->shipping_title. "\n";
        $sent_to_basecamp .= 'Payment Method: ' .core()->getConfigData('sales.paymentmethods.' . $order->payment->method . '.title'). "\n";

        foreach ($order->items as $key => $value) {
			$diamondInfo = $senderData->getDiamondInfo($value->sku);
			$diamondDeatails = "";
			$realCost = "";
			if(!empty($diamondInfo)){
				foreach($diamondInfo as $diamondRcd) {
					$diamondDeatails = " ( #".$diamondRcd->Sku." - ".$diamondRcd->name.", ".$diamondRcd->CertType." ". $diamondRcd->Style." SKU ".$diamondRcd->stockNumber." )";
					$markup_per = round(((($value->price/($diamondRcd->WholesalePrice))-1)*100),1);
					$realCost = "Real Cost: $".number_format($diamondRcd->WholesalePrice,2)."(".$markup_per."%) \n";
				}
			}
        $sent_to_basecamp .= "\n";
        $sent_to_basecamp .= 'Product: ' .$value->name.$diamondDeatails. "\n";
        if(isset($value->additional['metal_type_val']))
			$sent_to_basecamp .= 'Metal: ' .$value->additional['metal_type_val']. "\n";
        $sent_to_basecamp .= 'Quantity: ' .$value->qty_ordered. "\n";
        $sent_to_basecamp .= 'Product Price: ' .'$'.number_format($value->base_total,2). "\n";
		$sent_to_basecamp .= $realCost;

        }

        $sent_to_basecamp .= "\n";
        $sent_to_basecamp .= 'Sub Total: ' . '$' .number_format($order->sub_total,2). "\n";
        $sent_to_basecamp .= 'Tax: ' . '$' .number_format($order->tax_amount,2). "\n";
        $sent_to_basecamp .= 'Shipping & Handling: ' . '$' .number_format($order->shipping_amount,2). "\n";
        $sent_to_basecamp .= 'Grand Total: ' . '$' .number_format($order->grand_total,2) . "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email,'Basecamp');
                $message->subject('Invoice for your order');
            });
        }
    }
	
	public static function sitemapMail($data,$msg)
    {
        //$senderData = new BasecampMail();
        //$sender_data = $senderData->senderDetails();
        //$email = $sender_data['to_mail'];
		$email = Config::get('constant.SITEMAP_BASECAMP_MAIL');
		
        $sent_to_basecamp = '';
        $sent_to_basecamp = $data. "\n";
		$sent_to_basecamp .= "\n";
        $sent_to_basecamp .= "*** Sitemap is".$msg." updated today."."\n";

        if (!empty($sent_to_basecamp))
        {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email, 'Basecamp');
                $message->subject('Barkevs Sitemap');
            });
        }
    }
	
	public static function getDiamondInfo($productSku) {
		$diamondInfo = DB::select('select `custom_kdmdiamonds`.WholesalePrice,`custom_kdmdiamonds`.Style,`custom_kdmdiamonds`.stockNumber,`custom_kdmdiamonds`.vendor,`custom_kdmdiamonds`.Sku,`custom_kdmdiamonds`.CertType, `custom_kdmvendors`.name from `custom_kdmdiamonds` inner join `custom_kdmvendors` ON custom_kdmvendors.id = custom_kdmdiamonds.vendor and `custom_kdmdiamonds`.`Style` = ?', [$productSku]);
		//$shipping = ($shippingDays) ? reset($shippingDays)->shipdays : 7; 
		return $diamondInfo;
	}
	
	public static function paymentCancelMail($cart, $userId, $error_code)
    {
		
        $senderData = new BasecampMail();
		$billingaddress = $cart->billing_address;
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];

        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Payment Failed or Cancel details' . "\n";
        $sent_to_basecamp .= 'Payment Date: ' . date('F d, Y, h:i a', strtotime(date("Y-m-d H:i:s"))) . "\n";
        $sent_to_basecamp .= 'Name: ' . ucwords($billingaddress->first_name). ' '.ucwords($billingaddress->last_name). "\n";
        $sent_to_basecamp .= 'Email: ' . $billingaddress->email. "\n";
        $sent_to_basecamp .= 'Phone: ' . $billingaddress->phone. "\n";
		if(isset($error_code) && $error_code!=""){
			$sent_to_basecamp .= 'Error Code: ' . $error_code. "\n";
	    }

        foreach ($cart->items as $key => $value) {
        $sent_to_basecamp .= "\n";
        $sent_to_basecamp .= 'Product: ' .$value->product->name. "\n";
        $sent_to_basecamp .= 'SKU: ' .$value->product->sku. "\n";
		if(isset($value->additional['ring_size_val']))
			$sent_to_basecamp .= 'Ring Size: '. 'US '.$value->additional['ring_size_val']. "\n";
		if(isset($value->additional['metal_type_val']))
			$sent_to_basecamp .= 'Metal: ' .$value->additional['metal_type_val']. "\n";
        $sent_to_basecamp .= 'Quantity: ' .$value->additional['quantity']. "\n";
        $sent_to_basecamp .= 'Product Price: ' .'$'.number_format($value->base_total,2). "\n";
        }
        $sent_to_basecamp .= "\n";
        $sent_to_basecamp .= 'Sub Total: ' . '$' .number_format($cart->base_sub_total,2). "\n";
        $sent_to_basecamp .= 'Payment Via: ' .core()->getConfigData('sales.paymentmethods.' . $cart->payment->method . '.title'). "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['refferal_url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {  
           Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email,'Basecamp');
                $message->subject('Payment Failed or Cancel detailes');
            });
        }
    }
    
	public static function getProductDetails($productId) {
		
		$ProductskuInfo = DB::select('select sku, id, name, url_key, min_price, description,product_id from `product_flat` where `product_flat`.`product_id` = ?', [$productId]);
        return $ProductskuInfo;		
	}
	
	public static function WishlistMail($wishlist, $userDetails, $total)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];
		
		$sent_to_basecamp = '';
        $sent_to_basecamp = 'Wishlist Items' . "\n";
		$sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a', strtotime(date("Y-m-d H:i:s"))) . "\n";
		foreach($userDetails as $user) {
			$sent_to_basecamp .= 'Name: ' . ucwords($user->first_name). ' '.ucwords($user->last_name). "\n";
			$sent_to_basecamp .= 'Email: ' . $user->email. "\n";
		}        
        $sent_to_basecamp .= 'Total Items: ' .$total. "\n";
        foreach ($wishlist['engagement'] as $value) {
			$sent_to_basecamp .= "\n";
			$productInfos = $senderData->getProductDetails($value->additional['product_id']);
			
			if(!empty($productInfos)){
				$proInfo = $productInfos[0];
				$sent_to_basecamp .= 'Product: ' .$proInfo->name. "\n";
				$sent_to_basecamp .= 'SKU: ' .$proInfo->sku. "\n";
			}
			if(isset($value->additional['ring_size_val']) && $value->additional['ring_size_val']!="" && $value->additional['ring_size_val']!=0)
				$sent_to_basecamp .= 'Ring Size: '. 'US '.$value->additional['ring_size_val']. "\n";
			if(isset($value->additional['metal_type_val']) && $value->additional['metal_type_val'] != "")
				$sent_to_basecamp .= 'Metal: ' .$value->additional['metal_type_val']. "\n";
			$sent_to_basecamp .= 'Product Price: ' .'$'.number_format($value->product->price,2). "\n";
        }
		
		foreach ($wishlist['wedding'] as $value) {
			$sent_to_basecamp .= "\n";
			$productInfos = $senderData->getProductDetails($value->additional['product_id']);
			
			if(!empty($productInfos)){
				$proInfo = $productInfos[0];
				$sent_to_basecamp .= 'Product: ' .$proInfo->name. "\n";
				$sent_to_basecamp .= 'SKU: ' .$proInfo->sku. "\n";
			}
			if(isset($value->additional['ring_size_val']) && $value->additional['ring_size_val']!="" && $value->additional['ring_size_val']!=0)
				$sent_to_basecamp .= 'Ring Size: '. 'US '.$value->additional['ring_size_val']. "\n";
			if(isset($value->additional['metal_type_val']) && $value->additional['metal_type_val'] != "")
				$sent_to_basecamp .= 'Metal: ' .$value->additional['metal_type_val']. "\n";
			$sent_to_basecamp .= 'Product Price: ' .'$'.number_format($value->product->price,2). "\n";
        }
		
		foreach ($wishlist['jewelry'] as $value) {
			$sent_to_basecamp .= "\n";
			$productInfos = $senderData->getProductDetails($value->additional['product_id']);
			
			if(!empty($productInfos)){
				$proInfo = $productInfos[0];
				$sent_to_basecamp .= 'Product: ' .$proInfo->name. "\n";
				$sent_to_basecamp .= 'SKU: ' .$proInfo->sku. "\n";
			}
			if(isset($value->additional['ring_size_val']) && $value->additional['ring_size_val']!="" && $value->additional['ring_size_val']!=0)
				$sent_to_basecamp .= 'Ring Size: '. 'US '.$value->additional['ring_size_val']. "\n";
			if(isset($value->additional['metal_type_val']) && $value->additional['metal_type_val'] != "")
				$sent_to_basecamp .= 'Metal: ' .$value->additional['metal_type_val']. "\n";
			$sent_to_basecamp .= 'Product Price: ' .'$'.number_format($value->product->price,2). "\n";
        }
		
		foreach ($wishlist['diamond'] as $value) {
			$sent_to_basecamp .= "\n";
			$productInfos = $senderData->getProductDetails($value->additional['product_id']);
			
			if(!empty($productInfos)){
				$proInfo = $productInfos[0];
				$sent_to_basecamp .= 'Product: ' .$proInfo->name. "\n";
				$sent_to_basecamp .= 'SKU: ' .$proInfo->sku. "\n";
			}
			$sent_to_basecamp .= 'Product Price: ' .'$'.number_format($value->product->price,2). "\n";
        }
        $sent_to_basecamp .= "\n";
        $sent_to_basecamp .= 'Page URL: ' . $sender_data['page_url'] . "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $sender_data['user_agent'] . "\n";

        if (!empty($sent_to_basecamp)) {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email,'Basecamp');
                $message->subject('Your Wishlist Details');
            });
        }
    }
public static function sampleRequestMail($data)
    {
        $senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];
		
		$sent_to_basecamp = 'Barkevs Sample Request' . "\n";
        $sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a') . "\n";
        $sent_to_basecamp .= 'Name: ' . $data['name'] . "\n";
        $sent_to_basecamp .= 'Email: ' . $data['email'] . "\n";
        $sent_to_basecamp .= 'Sku: ' . $data['sku'] . "\n";
        $sent_to_basecamp .= 'Phone: ' . $data['phone'] . "\n";
        $sent_to_basecamp .= 'Page URL: ' . $data['page-url']. "\n";
        $sent_to_basecamp .= 'Referral URL: ' . $data['refferal-url'] . "\n";
        $sent_to_basecamp .= 'Request From: ' . $sender_data['request_from'] . "\n";
        $sent_to_basecamp .= 'Referred BY: ' . $sender_data['reffered_by'] . "\n";
        $sent_to_basecamp .= 'IP Address: ' . $sender_data['ip_address'] . "\n";
        $sent_to_basecamp .= 'Agents: ' . $data['user-agent'] . "\n";
		if (!empty($sent_to_basecamp))
        {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email, 'Basecamp');
                $message->subject('Sample Request');
            });
        }
	}

    public static function productImportMail($data)
    {
		$senderData = new BasecampMail();
        $sender_data = $senderData->senderDetails();
        $email = $sender_data['to_mail'];
        $sent_to_basecamp = '';
        $sent_to_basecamp = 'Data Imported Successfully' . "\n";
        $sent_to_basecamp .= 'Date: ' . date('F d, Y, h:i a') . "\n";
        $sent_to_basecamp .= 'Inserted Product: '.$data['insert_count']. "\n";
        $sent_to_basecamp .= 'Updated Product: '.$data['update_count']. "\n";
        $sent_to_basecamp .= 'File: '.$data['file']. "\n";

        if (!empty($sent_to_basecamp))
        {
            Mail::raw($sent_to_basecamp, function ($message) use($email){
                $message->from('info@barkevs.com', 'Barkevs');
                $message->to($email, 'Basecamp');
                $message->subject('Barkevs Data Import');
            });
        }
    }
  }