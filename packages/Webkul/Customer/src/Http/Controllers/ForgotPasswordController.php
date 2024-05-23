<?php

namespace Webkul\Customer\Http\Controllers;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Password;
use Webkul\Customer\Helpers\BasecampMail;
use Webkul\Customer\Repositories\CustomerRepository;
use Config;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Contains route related configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        CustomerRepository $customerRepository
    )
    {
        $this->_config = request('_config');

        $this->customerRepository = $customerRepository;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view($this->_config['view']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
		$data = request()->input();

        $customer = $this->customerRepository->findOneByField('email',$data['email']);
		if(empty($customer)){
		session()->flash('error',trans('customer::app.forget_password.email_not_exist'));
		return redirect()->back();
		}


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
        try {
            $this->validate(request(), [
                'email' => 'required|email',
            ]);

            $response = $this->broker()->sendResetLink(
                request(['email'])
            );

            if ($response == Password::RESET_LINK_SENT) {

                BasecampMail::forgetPassword($data);
                
                session()->flash('success', trans('customer::app.forget_password.reset_link_sent'));
                
                return back();
            }

            return back()
                ->withInput(request(['email']))
                ->withErrors([
                    'email' => trans('customer::app.forget_password.email_not_exist'),
                ]);
        } catch (\Swift_RfcComplianceException $e) {
            session()->flash('success', trans('customer::app.forget_password.reset_link_sent'));

            return redirect()->back();
        } catch (\Exception $e) {
            report($e);
            session()->flash('error', trans($e->getMessage()));

            return redirect()->back();
        }
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker('customers');
    }
}