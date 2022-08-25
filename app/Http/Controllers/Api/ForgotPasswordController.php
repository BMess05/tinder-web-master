<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWTAuth;
use App\Model\User;
use Illuminate\Support\Facades\Validator;
use App\Mail\ResetPasswordMail;
use Mail;

class ForgotPasswordController extends Controller
{
    protected $jwtAuth;
    function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
        $this->middleware('auth:api', ['except' => ['forgot_password', 'update_password']]);
        //
    }

    function forgot_password(Request $request)
    {

        $messages = [
            'email.required' => __('messages.email_invalid'),
            'email.email' => __('messages.email_invalid'),
            'email.exists' => __('messages.email_invalid'),
        ];
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ], $messages);

        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __($validator->messages()->first())
            ];
            return response()->json($res, 200, [], JSON_NUMERIC_CHECK);
        }

        $data = $request->all();
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => __('messages.email_invalid')], 200, [], JSON_NUMERIC_CHECK);
        }

        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => __('messages.email_not_registered')], 200, [], JSON_NUMERIC_CHECK);
        }

        $otp = $this->generateOtp();
        $lang = $user->content_language != '' ? $user->content_language : 'en';
        app()->setlocale($lang);
        $details = [
            'title' => __('messages.OTP_for_password_reset'),
            'otp' => $otp,
            'subject' => __('messages.forgot_password_subject')
        ];

        $res = User::where('email', $data['email'])->update(['otp' => $otp]);
        \Mail::to($data['email'])->send(new \App\Mail\ForgetPasswordMail($details));

        return response()->json(['success' => true, 'message' => __('messages.email_sent_for_OTP'), 'otp' => $otp], 200, [], JSON_NUMERIC_CHECK);
    }

    function generateOtp()
    {
        $otp = rand(1000, 999999);
        $res = $this->checkOTPUnique($otp);
        if ($res) {
            return $otp;
        } else {
            $this->generateOtp();
        }
    }

    function checkOTPUnique($otp)
    {
        $res = User::where('otp', $otp)->first();
        if ($res) {
            return false;
        } else {
            return true;
        }
    }

    function update_password(Request $request)
    {

        $messages = [
            'otp.required' => __('messages.invalid_OTP'),
        ];
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'otp' => 'required'
        ]);
        // print_r($validator); exit;
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __($validator->messages()->first())
            ];
            return response()->json($res, 200, [], JSON_NUMERIC_CHECK);
        }

        $data = $request->all();
        $res = User::where('otp', $data['otp'])->first();

        if ($res) {
            $password = bcrypt($data['password']);
            $res = User::where('otp', $data['otp'])->update(['password' => $password, 'otp' => null]);
            if ($res) {
                return response()->json(['success' => true, 'message' => __('messages.password_updated_successfully')], 200, [], JSON_NUMERIC_CHECK);
            } else {
                return response()->json(['success' => false, 'message' => __('messages.something_went_wrong')], 200, [], JSON_NUMERIC_CHECK);
            }
        } else {
            return response()->json(['success' => false, 'message' => __('messages.invalid_OTP')], 200, [], JSON_NUMERIC_CHECK);
        }
    }
}
