<?php

namespace Webkul\Customer\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Config;

class SampleRequestEmail extends Mailable
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
		$cc = Config::get('constant.MAIL_CC');
        $bcc = Config::get('constant.MAIL_BCC');
       return $this->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
            ->to($this->data['email'], $this->data['name'])
			->cc($cc)
			->bcc($bcc)
            ->subject('Thank you for sending sample request')
             ->view('shop::emails.customer.sample_request')->with('data', $this->data);

    }
}