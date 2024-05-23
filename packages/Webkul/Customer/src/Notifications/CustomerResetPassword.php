<?php

namespace Webkul\Customer\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;
use Config;

class CustomerResetPassword extends ResetPassword
{

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }
		$bcc = Config::get('constant.MAIL_BCC');
        return (new MailMessage)
            ->from(core()->getSenderEmailDetails()['email'], core()->getSenderEmailDetails()['name'])
			->bcc($bcc)
            ->subject(__('shop::app.mail.forget-password.subject') )
            ->view('shop::emails.customer.forget-password', [
                'user_name' => $notifiable->name,
                'email' => $notifiable->email,
                'token'     => $this->token,
                ]
            );
    }
}
