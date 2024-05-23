<?php

namespace Webkul\Customer\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Config;

class RegistrationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array
     */
    public $data;

    /**
     * Create a new mailable instance.
     *
     * @param  array  $data
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
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
            ->to($this->data['email'])
			->bcc($bcc)
            ->subject(trans('shop::app.mail.customer.registration.customer-registration'))
            ->view('shop::emails.customer.registration')->with('data', $this->data);
    }
}