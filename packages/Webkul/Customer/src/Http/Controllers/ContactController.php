<?php

namespace Webkul\Customer\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Webkul\Customer\Mail\ContactEmail;
use Webkul\Customer\Mail\SampleRequestEmail;
use Cookie;
use Webkul\Customer\Helpers\BasecampMail;
use Config;

class ContactController extends Controller
{
    
    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    public function __construct()
    {
        $this->_config = request('_config');
    }

    /**
     * Opens up the user's sign up form.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view($this->_config['view']);
    }
	
	public function send()
    {
        //return view($this->_config['view']);
		
		$this->validate(request(), [
            'name'    => 'string|required',
            'phone'   => 'required',
            'email'   => 'email|required',
			'comments' => 'required',
        ]);

        $data = request()->input();
		if(isset($data['agreement']) && ($data['agreement'] == "AllowUser" || $data['agreement'] == ""))
		{
			if(isset($data['g-recaptcha-response']) && !empty($data['g-recaptcha-response'])){
				$captcha=$data['g-recaptcha-response'];
				$secret = Config::get('constant.GOOGLE_RECAPTCHA_SECRET_KEY');
				$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']));
				if(isset($response->success) && $response->success === false)
				{
					session()->flash('error', 'Invalid captcha please try again!');
					return redirect()->back();
				}
			}else{
				session()->flash('error', 'Invalid captcha please try again!');
				return redirect()->back();
			}
			session()->flash('success', 'Contact email sent Thank You, We will get back to you soon!!');
			return redirect()->back();
		} else {
			if(isset($data['g-recaptcha-response']) && !empty($data['g-recaptcha-response'])){
				$captcha=$data['g-recaptcha-response'];
				$secret = Config::get('constant.GOOGLE_RECAPTCHA_SECRET_KEY');
				$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']));
				if(isset($response->success) && $response->success === false)
				{
					session()->flash('error', 'Invalid captcha please try again!');
					return redirect()->back();
				}
			}else{
				session()->flash('error', 'Invalid captcha please try again!');
				return redirect()->back();
			}
			session()->flash('success', trans('Thank You, We will get back to you soon!!'));
			
			try {
				
				Mail::queue(new ContactEmail($data));
				BasecampMail::contactMail($data);

				session()->flash('success', 'Contact email sent Thank You, We will get back to you soon!!');
			} catch (\Exception $e) {
				report($e);
				session()->flash('info', 'Contact email not sent!');
			}
			
			return redirect()->back();
		}
		/*echo 'data--<pre>';
		print_r($data);
		die;*/
    }
	
	public function send_sample()
    {
		$this->validate(request(), [
            'name'    => 'string|required',
            'phone'   => 'required',
            'email'   => 'email|required',
        ]);

        $data = request()->input();
		if(isset($data['agreement']) && ($data['agreement'] == "AllowUser" || $data['agreement'] == ""))
		{
			if(isset($data['g-recaptcha-response']) && !empty($data['g-recaptcha-response'])){
				$captcha=$data['g-recaptcha-response'];
				$secret = Config::get('constant.GOOGLE_RECAPTCHA_SECRET_KEY');
				$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']));
				if(isset($response->success) && $response->success === false)
				{
					session()->flash('error', 'Invalid captcha please try again!');
					return redirect()->back();
				}
			}else{
				session()->flash('error', 'Invalid captcha please try again!');
				return redirect()->back();
			}
			session()->flash('success', 'Sample Request email sent Thank You, We will get back to you soon!!');
			return redirect()->back();
		} else {
			if(isset($data['g-recaptcha-response']) && !empty($data['g-recaptcha-response'])){
				$captcha=$data['g-recaptcha-response'];
				$secret = Config::get('constant.GOOGLE_RECAPTCHA_SECRET_KEY');
				$response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']));
				if(isset($response->success) && $response->success === false)
				{
					session()->flash('error', 'Invalid captcha please try again!');
					return redirect()->back();
				}
			}else{
				session()->flash('error', 'Invalid captcha please try again!');
				return redirect()->back();
			}
			session()->flash('success', trans('Thank You, We will get back to you soon!!'));
			
			try {
				Mail::queue(new SampleRequestEmail($data));
				BasecampMail::sampleRequestMail($data);
				session()->flash('success', 'Sample Request email sent Thank You, We will get back to you soon!!');
			}catch (\Exception $e) {
				report($e);
				session()->flash('info', 'Sample Request email not sent!');
			}
			
			return redirect()->back();
		}
    }

}
