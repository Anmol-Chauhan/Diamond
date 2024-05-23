<?php

namespace Webkul\Customer\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Config;

class CustomerUpdatePassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The customer instance.
     *
     * @var  \Webkul\Customer\Models\Customer  $customer
     */
    public $customer;

    /**
     * Create a new message instance.
     *
     * @param  \Webkul\Customer\Models\Customer  $customer
     * @return void
     */
    public function __construct($customer)
    {
        $this->customer = $customer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
		$bcc = Config::get('constant.MAIL_BCC');
        return $this->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
                    ->to($this->customer->email, $this->customer->name)
					->bcc($bcc)
                    ->subject(trans('shop::app.mail.update-password.subject'))
                    ->view('shop::emails.customer.update-password', ['user' => $this->customer]);
    }
}