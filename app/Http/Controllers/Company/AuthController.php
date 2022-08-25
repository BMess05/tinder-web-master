<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Company;
use App\Events\CompanyForgetPassword;
use App\Http\Requests\PasswordSetRequest;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('guest')->except('logout');
        $this->middleware('guest:companies')->except('logout');
    }

    public function setPassword($email) {
        $email = urldecode($email);
        $company = Company::where('email', $email)->first();
        if(!$company) {
            echo "Invalid Link!!"; die;
        }
        if($company->is_verified == 1) {
            return redirect()->route('companyLogin')->with(['status' => 'danger', 'message' => 'Password already set, Please login to access your account.']);
        }
        return view('companies.auth.password_set', ['email' => $email]);
    }

    public function setCompanyPassword(PasswordSetRequest $request) {
        // $validatedData = $request->validate([
        //     'password' => 'required|string|min:8|confirmed'
        // ]);
        
        $data = $request->all();
        
        if(!isset($data['email'])) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something went wrong, Please try again.'])->withInput();
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid.'])->withInput();
        }
        $company = Company::where('email', $data['email'])->first();
        if(!$company) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid.'])->withInput();
        }

        $company->password = bcrypt($data['password']);
        $company->is_verified = 1;
        if($company->save()) {
            return redirect()->route('companyLogin')->with(['status' => 'success', 'message' => 'Password set successfully.']);
        }
        return redirect()->back()->with(['status' => 'danger', 'message' => 'Something went wrong, Please try again.'])->withInput();
    }

    public function login() {
        return view('companies.auth.login');
    }

    public function companySignIn(Request $request) {
        $this->validate($request, [
            'email'   => 'required|email',
            'password' => 'required|min:6'
        ]);

        $company = Company::where('email', $request->email)->first();
        if($company && $company->is_verified == 0) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'This account is not verified, Please check your email to set password and access your account.'])->withInput($request->only('email', 'remember'));
        }

        if (\Auth::guard('companies')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {

            return redirect()->intended('/company');
        }
        return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid credentails.'])->withInput($request->only('email', 'remember'));
    }

    public function logout( Request $request ) {
        if(\Auth::guard('companies')->check()) // this means that the admin was logged in.
        {
            \Auth::guard('companies')->logout();
            $request->session()->invalidate();
            return redirect()->route('companyLogin');
        }
        \Auth::guard()->logout();
        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/');
    }

    public function forget_password() { 
        return view('companies.auth.forget_password');
    }

    public function passwordResetMail(Request $request) {
        $this->validate($request, [
            'email'   => 'required|email|exists:companies,email'
        ]);
        $data = $request->all();
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid.'])->withInput();
        }
        $company = Company::where('email', $data['email'])->first();
        if(!$company) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Company not found.'])->withInput();
        }
        $company->password_reset_token = bcrypt($data['email']);
        $company->save();
        event(new CompanyForgetPassword($company));
        return redirect()->back()->with(['status' => 'success', 'message' => 'Please check your email to find link to reset password.']);
    }

    public function reset_password(Request $request) {
        $data = $request->all();
        $email = urldecode($data['email']);
        $token = urldecode($data['token']);

        $company = Company::where('email', $email)->first();
        if(!$company) {
            echo "Invalid Link!!"; die;
        }
        if($company->password_reset_token == "") {
            return redirect()->route('companyLogin')->with(['status' => 'danger', 'message' => 'Password already set, Please login to access your account.']);
        }
        return view('companies.auth.password_reset', ['email' => $email, 'token' => $token]);
    }

    public function update_password(PasswordSetRequest $request) {
        // $validatedData = $request->validate([
        //     'password' => 'required|string|min:8|confirmed'
        // ]);
        
        $data = $request->all();
        
        if(!isset($data['email'])) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something went wrong, Please try again.'])->withInput();
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid.'])->withInput();
        }
        if(!isset($data['token'])) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something went wrong, Please try again.'])->withInput();
        }
        $company = Company::where('email', $data['email'])->where('password_reset_token', $data['token'])->first();
        if(!$company) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Token is invalid.'])->withInput();
        }

        $company->password = bcrypt($data['password']);
        $company->password_reset_token = "";
        if($company->save()) {
            return redirect()->route('companyLogin')->with(['status' => 'success', 'message' => 'Password reset successfully.']);
        }
        return redirect()->back()->with(['status' => 'danger', 'message' => 'Something went wrong, Please try again.'])->withInput();
    }
}
