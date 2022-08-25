<?php

namespace App\Http\Controllers\Api;

use App\Events\AssignDefaultPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\JWTAuth;
use App\Model\User;
use App\Model\DeviceToken;
use App\Model\Category;
use App\Model\Interest;
use App\Model\LikeDislike;
use App\Model\Subscriptions;
use App\Model\SubscriptionFeaturesStatus;
use Illuminate\Support\Facades\Validator;
use App\Mail\ResetPasswordMail;
use Mail;
use App\CommonHelpers;

class RegisterController extends Controller
{
    protected $jwtAuth;
    function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
        $this->middleware('auth:api', ['except' => ['register', 'deleteUser']]);
        //
    }
    function selectFields($language)
    {
        if ($language == 'en') {
            $fields = ['id', 'title'];
        } elseif ($language == 'de') {
            $fields = ['id', 'title_de as title'];
        } else {
            $fields = ['id', 'title_tr as title'];
        }
        return $fields;
    }

    public function register(Request $request)
    {
        $messages = [
            'email.unique' => __('messages.email_already_taken')
        ];

        $validator = Validator::make($request->all(), [
            // 'email' => 'required|email|unique:users,email,{$id},id,deleted_at,NULL',
            'email'    => [
                'required', Rule::unique('users')->where(function ($query) {
                    return $query->where('deleted_at', '=', null);
                })
            ],
            'password' => 'required|min:8',
            'device_type' => "required",
            'device_token' => "required"
        ]);

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

        try {
            $todayDate = date("Y-m-d H:i:s");
            $expiryDateTime = date("Y-m-d H:i:s", strtotime("+60 minutes", strtotime($todayDate)));

            // $user_1 = User::withTrashed()->where('email', $data['email'])->first();

            // if($user_1) {
            //     if($user_1->deleted_at != NULL) {
            //         $user = User::withTrashed()->find($user_1->id);
            //         $user->deleted_at = NULL;
            //         $result = $user->save();
            //         if($result) {
            //             $token = $this->jwtAuth->fromUser($user);
            //             $user_info = $user->toArray();

            //             $interests = [];
            //             if($user_info['interests'] != null) {
            //                 $interest_ids = explode(',', $user_info['interests']);
            //                 foreach($interest_ids as $int_id) {
            //                     $language=$request->header('Content-Language');
            //                     $fields=$this->selectFields($language);
            //                     $interest = Interest::select($fields)->where('id', $int_id)->first();
            //                     if($interest) {
            //                         $interests[] = $interest->toArray();
            //                     }
            //                 }
            //             }
            //             $user_info['interests'] = $interests;

            //             $details = [
            //                 'title' => __('messages.registration_successfull'),
            //                 'user' => $user_info
            //             ];

            //             // \Mail::to($user_info['email'])->send(new \App\Mail\RegistrationMail($details));

            //             $res = ['success' => true, 'message' => __('messages.registerd_successfully'), 'token' => $token, 'user' => $user_info];
            //         }   else {
            //             $res = ['success' => false, 'message' => 'Something went wrong, please try again.'];
            //         }
            //         return response()->json($res,200,[],JSON_NUMERIC_CHECK);
            //     }
            // }

            $rand_no = $this->generate_email_verification_code();
            $user = new User();
            // $user->name = $data['name'];
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->type = "1";
            $user->is_verified = "0";
            $user->email_verification_token = $rand_no;
            $user->otp = CommonHelpers::generateOtp(6);
            $user->otp_expiration_time = $expiryDateTime;
            $user->show_my_gender = 1;
            $result = $user->save();
            if ($result) {

                /* Save device or update device token */
                if (isset($data['device_token']) && isset($data['device_type'])) {
                    if (trim($data['device_token']) != "" && trim($data['device_type']) != "" && in_array($data['device_type'], [1, 2])) {
                        $already_login = DeviceToken::where(['device_token' => $data['device_token'], 'device_type' => $data['device_type']])->first();
                        if ($already_login) {
                            $device = DeviceToken::find($already_login->id);
                        } else {
                            $device = new DeviceToken();
                        }

                        $device->user_id = $user->id;
                        $device->device_type = $data['device_type'];
                        $device->device_token = $data['device_token'];
                        if (!$device->save()) {
                            $res = ['success' => false, 'message' => __('messages.could_not_save_device_token')];
                            return response()->json($res, 200, [], JSON_NUMERIC_CHECK);
                        }
                    }
                }

                /* send mail with random code for verification.... */
                Mail::send(
                    'email.email_verify_otp',
                    ['details' => $user->toArray(), 'msg' => 'Please use the OTP to verify your account.'],
                    function ($m) use ($user) {
                        // $m->from(env('MAIL_FROM_ADDRESS'),env('YOUR_APP_NAME'));
                        // $m->to($params['email'])->subject('Reset Your Password');
                        $m->from(config('mail.from.address'), config('app.name'));
                        $m->to($user->email)->subject('Tündür: OTP for account verification');
                    }
                );
                \Log::info("Verification Mail sent time: " . \Carbon\Carbon::now());


                $token = $this->jwtAuth->fromUser($user);
                $user = User::with('user_images')->where('id', $user->id)->first();
                $user = User::select(\DB::raw('*, DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT(dob, "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT(dob, "00-%m-%d")) AS age'))
                    ->with('user_images')->where('id', $user->id)->first();
                if (!$user) {
                    $res = [
                        'success' => false,
                        'message' => __('messages.invalid_user_id')
                    ];
                    return response()->json($res);
                }
                $user_info = $user->toArray();
                $user_info['content-language'] =  $request->header('Content-Language');
                $user = User::find($user->id);
                $user->content_language = $request->header('Content-Language');
                $user->save();
                if (count($user_info['user_images']) > 0) {
                    $imgs = [];
                    for ($i = 0; $i < count($user_info['user_images']); $i++) {
                        $user_info['user_images'][$i]['image_path'] = url('/uploads/users/' . $user_info['user_images'][$i]['image_name']);
                    }
                }

                /****************** */
                $today_super_likes_count = LikeDislike::where(['sender_id' => $user_info['id'], 'is_super' => 1])
                    ->whereRaw('Date(updated_at) = CURDATE()')
                    ->get()->count();
                $user_info['remaining_super_like_count'] = 5 - $today_super_likes_count;

                /****************************/

                $details = [
                    'title' => __('messages.registration_successful'),
                    'user' => $user_info
                ];

                // \Mail::to($user_info['email'])->send(new \App\Mail\RegistrationMail($details));

                //trigger AssignDefaultPlan event
                $eventData = ['platform' => $data['device_type'], 'plan_id' => 1, 'transaction_id' => 0, 'product_id' => '0'];
                event(new AssignDefaultPlan($user, $eventData));


                /****************************/

                /****Get available plan and available features of user****/
                $activeSubscription  = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                    ->where('subscriptions.user_id', $user->id)
                    ->where('is_active', 1)
                    ->first();
                /*********************************************************/

                $res = ['success' => true, 'message' => __('messages.registered_successfully'), 'token' => $token, 'user' => $user_info, 'otp_expiry_time' => $expiryDateTime, 'subscription_info' => $activeSubscription];
            } else {
                $res = ['success' => false, 'message' => 'Something went wrong, please try again.'];
            }
            return response()->json($res, 200, [], JSON_NUMERIC_CHECK);
        } catch (Exception $e) {
            report($e);
            return response()->json(array('success' => false, 'message' => $e->getMessage()));
        }
    }

    public function deleteUser()
    {
        $user = User::whereNotIn('id', [211, 212, 213, 214, 215, 216, 217])->delete();
        return response()->json(array('success' => true, 'message' => 'Yeah Deleted'));
    }

    public function generate_email_verification_code()
    {
        $x = 32;
        $rand_no =  substr(str_shuffle("01234567890123456789012345678928"), 0, $x);
        $user = User::where('email_verification_token', $rand_no)->first();
        if ($user) {
            $this->generate_email_verification_code();
        } else {
            return $rand_no;
        }
    }
}
