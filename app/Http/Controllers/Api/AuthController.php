<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\JWTAuth;
use App\Model\User;
use App\Model\DeviceToken;
use App\Model\Log;
use App\Model\Interest;
use App\Model\LikeDislike;
use App\Model\Subscriptions;
use App\Model\SubscriptionFeaturesStatus;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    protected $jwtAuth;
    public function __construct( JWTAuth $jwtAuth )
    {
        $this->jwtAuth = $jwtAuth;
        $this->middleware('auth:api', ['except' => ['login', 'logout']]);
    }

    function selectFields($language)
    {
         if ($language=='en') {
            $fields=['id', 'title'];
        }elseif ($language=='de') {
            $fields=['id', 'title_de as title'];
        }else{
            $fields=['id', 'title_tr as title'];
        }
        return $fields;
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res);
        }

        $data = $request->all();
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['success' => false, 'message' => __('messages.email_address_invalid')]);
        }

        $user = User::where('email', $data['email'])->first();
        if(!$user) {
            return response()->json(['success' => false, 'message' => __('messages.email_not_registered')]);
        }
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            if($user->is_blocked == 1) {
                $log = new Log();
                $log->user_id = $user->id;
                $log->type = 1;
                $log->description = "Login attempt on blocked account";
                $log->status = "failed";
                $log->save();
                return response()->json(['success' => false, 'message' => __('messages.your_account_is_blocked')]);
            }
            $token = $this->jwtAuth->fromUser($user);
            if(isset($data['device_token']) && isset($data['device_type'])) {
                if(trim($data['device_token']) != "" && trim($data['device_type']) != "" && in_array($data['device_type'], [1, 2])) {
                    $already_login = DeviceToken::where(['device_token' => $data['device_token'], 'device_type' => $data['device_type']])->first();
                    if($already_login) {
                        $device = DeviceToken::find($already_login->id);
                    }   else {
                        $device = new DeviceToken();
                    }
                    
                    $device->user_id = auth()->user()->id;
                    $device->device_type = $data['device_type'];
                    $device->device_token = $data['device_token'];
                    if(!$device->save()) {
                        $res = ['success' => false, 'message' => __('messages.could_not_save_device_token')];
                        return response()->json($res);
                    }
                }
            }
            $user = User::find(auth()->user()->id);
            $user->content_language = $request->header('Content-Language');
            $user->save();
            $user = User::with('user_images')->where('id', auth()->user()->id)->first();
            $user = User::select(\DB::raw('*, DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT(dob, "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT(dob, "00-%m-%d")) AS age'))
            ->with('user_images')->where('id', auth()->user()->id)->first();
            if(!$user) {
                $res = [
                    'success' => false,
                    'message' => __('messages.invalid_user_id')
                ];
                return response()->json($res);
            }
            $user_info = $user->toArray();
            $user_info['content-language'] =  $request->header('Content-Language');
            
            if(count($user_info['user_images']) > 0) {
                $imgs = [];
                for($i = 0; $i < count($user_info['user_images']); $i++) {
                    $user_info['user_images'][$i]['image_path'] = url('/uploads/users/'.$user_info['user_images'][$i]['image_name']);
                }
            }

            if($user->user_picture != null) {
                $user->user_picture = url('/uploads/users/'.$user->user_picture);
            } 

            $interests = [];
            if($user_info['interests'] != null) {
                $interest_ids = explode(',', $user_info['interests']);
                foreach($interest_ids as $int_id) {
                    $language=$request->header('Content-Language');
                    $fields=$this->selectFields($language);
                    $interest = Interest::select($fields)->where('id', $int_id)->first();
                    if($interest) {
                        $interests[] = $interest->toArray();
                    }
                }
            }
            $user_info['interests'] = $interests;
            /****************** */
            $today_super_likes_count = LikeDislike::where(['sender_id' => $user_info['id'], 'is_super' => 1])
            ->whereRaw('Date(updated_at) = CURDATE()')
            ->get()->count();
            $user_info['remaining_super_like_count'] = 5 - $today_super_likes_count;
            

            /****Get available plan and available features of user****/
            $activeSubscription  = Subscriptions::with('user_features_status','subscription_plan','consumables')
                                                ->where('subscriptions.user_id',$user->id)
                                                ->where('is_active',1)
                                                ->first();
            /*********************************************************/

            //set is_online status to 1
            User::where('id', auth()->user()->id)->update(['is_online'=>1]);            

            $log = new Log();
            $log->user_id = $user->id;
            $log->type = 1;
            $log->description = "Login attempt success";
            $log->status = "success";
            $log->save();
            $res = ['success' => true, 'message' => __('messages.login_success'), 'token' => $token, 'user' => $user_info,'subscription_info'=>$activeSubscription];
            // return $this->respondWithToken($token);
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        } 
        $log = new Log();
        $log->user_id = $user->id;
        $log->type = 1;
        $log->description = "Login attempt failed. Wrong Password.";
        $log->status = "failed";
        $log->save();
        $res = ['success' => false, 'message' => __('messages.unauthorized')];
        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user(),200,[],JSON_NUMERIC_CHECK);
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $data = $request->all();

        // dd($data);
        
        //set is_online status to 0
        User::where('id', $data['user_id'])->update(['is_online'=>0]);
        
        if(isset($data['device_token']) && isset($data['device_type'])) {
            if(trim($data['device_token']) != "" && trim($data['device_type']) != "" && in_array($data['device_type'], [1, 2])) {
                $resp = DeviceToken::where(['device_token' => $data['device_token'], 'device_type' => $data['device_type']])->delete();
                if(!$resp) {
                    $res = ['success' => false, 'message' => __('messages.already_logout')];
                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
            }
        }
        
        $this->guard()->logout();
        
        return response()->json(['message' => 'Successfully logged out.'],200,[],JSON_NUMERIC_CHECK);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}