<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;
use Hash,Mail;
use Tymon\JWTAuth\JWTAuth;
use App\Model\User;
use App\Model\DeviceToken;
use App\Model\UserImage;
use App\Model\LikeDislike;
use App\Model\Match;
use App\Model\ChatMessage;
use App\Model\Report;
use App\Model\ReportReason;
use App\Model\Interest;
use App\Model\Subscriptions;
use App\Model\SubscriptionPlans;
use App\Model\SubscriptionFeaturesStatus;
use App\Model\ConsumableFeatures;
use App\Model\Feedback;
use Illuminate\Support\Facades\Validator;
use App\Jobs\UpdateLocation;
use Illuminate\Support\Facades\DB;
use App\CommonHelpers;
use App\FirebaseHelper;
use Carbon\Carbon;

use App\Model\SubscriptionReceipts;
use App\Model\UserTopPickup;
use App\Model\Log;

class UserController extends Controller
{
    protected $jwtAuth;
    function __construct( JWTAuth $jwtAuth ) {
        $this->jwtAuth = $jwtAuth;
        $this->middleware('auth:api', ['except' => ['upload_image','update_language', 'update_profile', 'searchUsers', 'like_dislike', 'get_profile', 'report_user', 'set_online_status', 'get_my_matches', 'top_ten_likes', 'unmatch', 'getAllConversations', 'set_show_my_gender', 'save_message', 'get_chat', 'get_who_like_me', 'upload_chat_image', 'get_who_super_like_me', 'set_app_notification', 'deleteAccount', 'save_interest', 'all_interests','verifyEmailWithOtp','resendOtp','virtualLocation','addFeedback','getAllAvailablePlansList','setMessagesRead','totalUnread']]);
    }

    function upload_image(Request $request) {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image' //|mimes:jpeg,jpg,png'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res);
        }


        $file = $request->file('image');

        // return $file->getClientMimeType();

        $full_name = $file->getClientOriginalName();
        $name = time() . '-' . $full_name;
        $path = public_path('/uploads/users');
        $file_r = $file->move($path, $name);
        $path = url('/uploads/users/'.$name);
        $res = [
            'success' => true,
            'message' => __('messages.image_uploaded_successfully'),
            'image_path' => $path,
            'image_name' => $name
        ];
        return response()->json($res);

    }

    function update_language(Request $request) {
        $auth_user = auth()->user()->toArray();
        $user = User::find($auth_user['id']);
        $user->content_language = $request->header('Content-Language');
        $result = $user->save();
        if($result){
            $lang = $request->header('Content-Language');
            app()->setlocale($lang);
            $res = ['success' => true, 'message' => __('messages.language_updated_successfully')];
        }else{
            $res = ['success' => false, 'message' => __('messages.something_went_wrong')];
        }
        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }
    function update_profile(Request $request) {
        $auth_user = auth()->user()->toArray();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'dob' => 'required|date',
            'gender' => 'required|in:1,2,3', //M,F,O
            // 'university' => 'required',
            // 'business' => 'required',
            'interested_in' => 'required|in:1,2,3,4',
            'show_my_gender' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res);
        }

        $data = $request->all();
        $user = User::find($auth_user['id']);
        $user->name = $data['name'];
        $user->dob = date('Y-m-d', strtotime($data['dob']));
        $user->gender = $data['gender'];

        $user->university = $data['university'];
        $user->business = $data['business'];

        $user->interested_in = $data['interested_in'];
        $user->show_my_gender = $data['show_my_gender'];
        if(array_key_exists('about_me',$data)){
            $user->about_me = $data['about_me'];
        }
        if(isset($data['city']) && trim($data['city']) != "") {
            $user->city = $data['city'];
        }
        $user->company = $data['company'];

        if(isset($data['interests']) && !empty($data['interests'])) {
            foreach($data['interests'] as $index => $inter) {
                $int = Interest::find($inter);
                if(!$int) {
                    unset($data['interests'][$index]);
                }
            }
            $interests = implode(',', $data['interests']);

            $user->interests = $interests;
        }   else {
            $user->interests = "";
        }


        $result = $user->save();
        // return $result;
        if($result) {
            if(isset($data['images']) && count($data['images']) > 0) {
                $rs = UserImage::where('user_id', $auth_user['id'])->delete();
                $user_images = UserImage::where('user_id', $auth_user['id'])->get();
                foreach($data['images'] as $img) {
                    $uimage = new UserImage();
                    $uimage->user_id = $auth_user['id'];
                    $uimage->image_name = $img;
                    if($user_images->count() < 1) {
                        $uimage->is_main = 1;
                    }
                    $uimage->save();
                }
            }
            // $token = $this->jwtAuth->fromUser($user);
            // $user = User::with('user_images')->where('id', auth()->user()->id)->first();
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
            // $user_info['interested_in'] = (int) $user_info['interested_in'];
            $user_info['content-language'] =  $request->header('Content-Language');

            if(count($user_info['user_images']) > 0) {
                $imgs = [];
                for($i = 0; $i < count($user_info['user_images']); $i++) {
                    $user_info['user_images'][$i]['image_path'] = url('/uploads/users/'.$user_info['user_images'][$i]['image_name']);
                }
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
            $today_super_likes_count = LikeDislike::where(['sender_id' => auth()->user()->id, 'is_super' => 1])
            ->whereRaw('Date(updated_at) = CURDATE()')
            ->get()->count();
            $user_info['remaining_super_like_count'] = 5 - $today_super_likes_count;

            /****************************/

            //get user subscription features info
            $subscription_info  = Subscriptions::with('user_features_status','subscription_plan','consumables')
                                            ->where('subscriptions.user_id',$user->id)
                                            ->where('is_active',1)
                                            ->first();
            $res = ['success' => true, 'message' => __('messages.profile_updated_successfully'), 'user' => $user_info,'subscription_info' => $subscription_info];
        }   else {
            $res = ['success' => false, 'message' => __('messages.something_went_wrong')];
        }
        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }

    function searchUsers(Request $request) {
        // echo 'https://maps.googleapis.com/maps/api/geocode/json?latlng=30.710855056975166,76.68634253842502&sensor=false&key=AIzaSyC9_aECcOQYBXTqJdeff9N4P87jrkL5tY8';
        $header_lang = $request->header('Content-Language');
        //dd($header_lang);
        $langText = "reason_text_".$header_lang;
        $reasons = ReportReason::select("id", $langText." as reason_text")->get();
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
            'custom_latitude' => 'required',
            'custom_longitude' => 'required',
            'men' => 'required|in:0,1',
            'women' => 'required|in:0,1',
            'other' => 'required|in:0,1',
            'start_age' => 'required',
            'end_age' => 'required',
            'distance' => 'required'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first()),
                'reasons' => $reasons->toArray()
            ];
            return response()->json($res);
        }

        $data = $request->all();
        if($data['end_age'] < $data['start_age']) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_range_of_age'),
                'reasons' => $reasons->toArray()
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }
        $current_user = User::find(auth()->user()->id);
        if(!$current_user) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_user_id'),
                'reasons' => $reasons->toArray()
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }
        $current_user->longitude = $data['longitude'];
        $current_user->latitude = $data['latitude'];
        $current_user->save();


        $updateLocationJob = new UpdateLocation(auth()->user()->id, $data['latitude'], $data['longitude']);
        dispatch($updateLocationJob);


        $current_lat = $data['custom_latitude'];
        $current_long = $data['custom_longitude'];

        $prefered_gender = [];
        if($data['men']  == 1) {
            $prefered_gender[] = "1";
        }
        if($data['women']  == 1) {
            $prefered_gender[] = "2";
        }
        if($data['other'] == 1) {
            $prefered_gender[] = "3";
        }

        $now_date = date('y-m-d');
        //  To search by kilometers instead of miles, replace 3959 with 6371.
        $searchResult = User::with('user_subscription_features')->select(\DB::raw('*, ( 6371 * acos( cos( radians('.$current_lat.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$current_long.') ) + sin( radians('.$current_lat.') ) * sin( radians( latitude ) ) ) ) AS distance, DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT(dob, "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT(dob, "00-%m-%d")) AS age'))
            // donot show user which are liked disliked by current user and by whom current user is liked or disliked
            ->with('user_images')
            ->whereNotIn('id',function($query) {
                $query->select('receiver_id')->where('sender_id', auth()->user()->id)->from('like_dislike');
            })
            ->whereNotIn('id',function($query) {
                $query->select('sender_id')->where(['receiver_id' => auth()->user()->id, 'like_dislike' => 0])->from('like_dislike');
            })
            ->whereNotIn('id',function($query) {
                $query->select('reported_id')->where('reported_by', auth()->user()->id)->from('reports');
            })
            ->whereNotIn('id',function($query) {
                $query->select('reported_by')->where('reported_id', auth()->user()->id)->from('reports');
            })
            ->where('id', '!=', auth()->user()->id)
            ->having('distance', '<=', $data['distance'])
            ->having('age', '<=', $data['end_age'])
            ->having('age', '>=', $data['start_age'])
            ->whereIn('gender', $prefered_gender)
            ->orderBy('distance','ASC')
            ->offset(0)
            ->limit(300)
            ->get()->toArray();

        foreach($searchResult as $index => $user_info) {
            if(count($user_info['user_images']) > 0) {
                for($i = 0; $i < count($user_info['user_images']); $i++) {
                    $searchResult[$index]['user_images'][$i]['image_path'] = url('/uploads/users/'.$user_info['user_images'][$i]['image_name']);
                }
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
            unset($searchResult[$index]['interests']);
            $searchResult[$index]['interests'] = $interests;

            //move boosted profiles on top of stack if boosted within last 30 minutes
            if( $user_info['user_subscription_features'] != null ){
                if($user_info['user_subscription_features']['last_boosted_on']){
                   $last_boosted_on = new Carbon( $user_info['user_subscription_features']['last_boosted_on'] );
                    //echo $last_boosted_on->diffInMinutes(Carbon::now());echo '<br />';
                    if( $last_boosted_on->diffInMinutes(Carbon::now()) <= 30 ){
                        $boostedProfile = $user_info;
                        unset( $searchResult[$index]['user_subscription_features']);
                        unset($searchResult[$index]);
                        array_unshift($searchResult,$boostedProfile);
                    }
                }

            }
        }
        //exit;

        // $var = new Carbon( $searchResult[10]['user_subscription_features']['boost_reset_on'] );
        // dd($var,Carbon::now());
        // dd(Carbon::now());
        // dd($var->greaterThan(Carbon::now()));

        $current_user = $current_user->load('subscriptions','user_subscription_features','subscription_plan','consumables');

        if($searchResult) {
            $res = ['success' => true, 'message' => __('messages.users_found'), 'data' => $searchResult, 'reasons' => $reasons->toArray(),'user'=>$current_user];
        }   else {
            $res = ['success' => false, 'message' => __('messages.no_user_found'), 'reasons' => $reasons->toArray()];
        }
        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }

    function like_dislike(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'like_dislike' => 'required|in:0,1',
            'super' => 'required|in:0,1'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res);
        }

        $data = $request->all();
        if($data['user_id'] == auth()->user()->id) {
            $res = [
                'success' => false,
                'message' => __('messages.cannot_like_unlike_own_profile')
            ];
            return response()->json($res);
        }
        $already_like_dislike = LikeDislike::where(['sender_id' => auth()->user()->id, 'receiver_id' => $data['user_id']])->first();
        if($already_like_dislike) {
            $ld_obj = LikeDislike::find($already_like_dislike->id);
        }   else {
            $ld_obj = new LikeDislike();
        }
        $ld_obj->sender_id = auth()->user()->id;
        $ld_obj->receiver_id = $data['user_id'];
        $ld_obj->like_dislike = $data['like_dislike'];
        if($data['super'] == 1) {
            $today_super_likes_count = LikeDislike::where(['sender_id' => auth()->user()->id, 'is_super' => 1])
            ->whereRaw('Date(updated_at) = CURDATE()')
            ->get()->count();
            if($today_super_likes_count > 4) {
                $res = [
                    'success' => false,
                    'message' => __('messages.super_like_limit_already_complete')
                ];
                unset($ld_obj);
                return response()->json($res);
            }
        }
        $ld_obj->is_super = $data['super'];

        if($ld_obj->save()) {
            if($data['like_dislike'] == 1) {

                $row = LikeDislike::where(['sender_id' => $data['user_id'], 'receiver_id' => auth()->user()->id, 'like_dislike' => 1])->first();
                if($row) {

                    $already_match = Match::whereIn('person_1', [auth()->user()->id, $data['user_id']])
                        ->whereIn('person_2', [$data['user_id'], auth()->user()->id])
                        ->first();
                    if($already_match) {
                        $res = ['success' => true, 'message' => __('messages.already_match'), 'match' => 0];
                    }   else {
                        $thread_id = gen_uuid();
                        $mobj = new Match();
                        $mobj->thread_id = $thread_id;
                        $mobj->person_1 = auth()->user()->id;
                        $mobj->person_2 = $data['user_id'];
                        if($mobj->save()) {
                            $receiver = User::with(['user_images'])->find($data['user_id'])->toArray();
                            $r_imgs = [];
                            if(count($receiver['user_images']) > 0) {
                                foreach($receiver['user_images'] as $img1) {
                                    $r_imgs[] = url('/uploads/users/'.$img1['image_name']);
                                }
                            }
                            $res = ['success' => true, 'message' => __('messages.its_a_match'), 'match' => 1, 'data' => ['conversation_id' => $thread_id, 'receiver_id' => $data['user_id'], 'receiver_name' => $receiver['name'], 'receiver_images' => $r_imgs, 'last_text' => '', 'last_image' => '', 'is_online' => $receiver['is_online']]];
                        }   else {
                            $res = ['success' => false, 'message' => __('messages.could_not_create_match')];
                        }
                    }
                }   else {
                    $res = ['success' => true, 'message' => __('messages.profile_liked'), 'match' => 0];
                }

            }   else {
                $match = Match::where(['person_1' => auth()->user()->id, 'person_2' => $data['user_id']])
                                ->orWhere(['person_1' => $data['user_id'], 'person_2' => auth()->user()->id])
                                ->first();
                if($match) {
                    $result = Match::where('id', $match->id)->delete();
                    $result_c = ChatMessage::where('thread_id', $match->thread_id);
                }
                $res = ['success' => true, 'message' => __('messages.profile_disliked'), 'match' => 0];
            }
        }   else {
            $res = ['success' => false, 'message' => __('messages.action_failed')];
        }
        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }

    function get_profile($user_id = null, Request $request) {

        $header_lang = $request->header('Content-Language');
        //dd($header_lang);

        $authUser = auth()->user();

        CommonHelpers::updateFeatureCount();
        $langText = "reason_text_".$header_lang;
        $reasons = ReportReason::select("id", $langText." as reason_text")->get();

        if($user_id == null) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_user_id')
            ];
            return response()->json($res);
        }
        $lat1 = $authUser->latitude;
        $lon1 = $authUser->longitude;

        $user = User::select(\DB::raw('*, DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT(dob, "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT(dob, "00-%m-%d")) AS age'))
        ->with('user_images')->where('id', $user_id)->first();

        if(!$user) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_user_id')
            ];
            return response()->json($res);
        }
        $user_info = $user->toArray();
        $user_info['content-language'] = $request->header('Content-Language');

        $lat2 = $user_info['latitude'];
        $lon2 = $user_info['longitude'];

        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            $distance = 0;
        }
        else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $distance = ($miles * 1.609344);

        }



        if(count($user_info['user_images']) > 0) {
            $imgs = [];
            for($i = 0; $i < count($user_info['user_images']); $i++) {
                $user_info['user_images'][$i]['image_path'] = url('/uploads/users/'.$user_info['user_images'][$i]['image_name']);
            }
        }
        $interests = [];
        if($user_info['interests'] != null) {
            $interest_ids = explode(',', $user_info['interests']);
            foreach($interest_ids as $int_id) {
                $language=$request->header('Content-Language');
                $fields=$this->selectFields($language);
                $interest = Interest::select($fields)->where(['id'=>$int_id])->first();
                if($interest) {
                    $interests[] = $interest->toArray();
                }
            }
        }

        $user_info['interests'] = $interests;
        $user_info['distance'] = (string) $distance;

        /****************** */
        $today_super_likes_count = LikeDislike::where(['sender_id' => auth()->user()->id, 'is_super' => 1])
        ->whereRaw('Date(updated_at) = CURDATE()')
        ->get()->count();
        $user_info['remaining_super_like_count'] = 5 - $today_super_likes_count;

        /****************************/
        $res = ['success' => true, 'message' => __('messages.user_info_found'), 'user' => $user_info, 'reasons'=>$reasons->toArray() ];

        if( $authUser->id == $user_id) //if same user the add subscription information
        $res['subscription_info']  = Subscriptions::with('user_features_status','subscription_plan','consumables')
                                                ->where('subscriptions.user_id',$user->id)
                                                ->where('is_active',1)
                                                ->first();

        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }

    function report_user(Request $request) {

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'report_reason' => 'required|exists:report_reasons,id',
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res);
        }

        $data = $request->all();
        if($data['user_id'] == auth()->user()->id) {
            $res = [
                'success' => false,
                'message' => __('messages.cannot_report_own_profile')
            ];
            return response()->json($res);
        }


        $user = User::find($data['user_id']);
        if(!$user) {
            $res = ['success' => false, 'message' => __('messages.invalid_reported_id')];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }
        $repObj = new Report();
        $repObj->reported_by = auth()->user()->id;
        $repObj->reported_id = $data['user_id'];
        $repObj->report_reason = $data['report_reason'];
        if($repObj->save()) {
            $match = Match::where(['person_1' => auth()->user()->id, 'person_2' => $data['user_id']])
                            ->orWhere(['person_1' => $data['user_id'], 'person_2' => auth()->user()->id])
                            ->first();
            if($match) {
                $result = Match::where('id', $match->id)->delete();
                $result_c = ChatMessage::where('thread_id', $match->thread_id);
            }

            $like_dislike = LikeDislike::where(['receiver_id' => auth()->user()->id, 'sender_id' => $data['user_id']])
                                ->orWhere(['receiver_id' => $data['user_id'], 'sender_id' => auth()->user()->id])
                                ->delete();
            return response()->json(['success' => true, 'message' => __('messages.reported_successfully')]);
        }   else {
            return response()->json(['success' => false, 'message' => __('messages.action_failed')]);
        }
    }

    function set_online_status($is_online = 0) {
        if($is_online == "" || !in_array($is_online, [0,1])) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_online_status')
            ];
            return response()->json($res);
        }
        $user = User::find(auth()->user()->id);
        $user->is_online = $is_online;
        if($user->save()) {
            return response()->json(['success' => true, 'message' => __('messages.status_updated_successfully')]);
        }   else {
            return response()->json(['success' => false, 'message' => __('messages.action_failed')]);
        }
    }

    function get_my_matches() {
        $matches = User::with('user_images')
        ->where(['type' => 1, 'is_blocked' => 0])
        ->whereIn('id', function($query) {
            $query->select('person_1')->where(['person_2' => auth()->user()->id])
            ->from('matches');
        })
        ->orWhereIn('id',function($query) {
            $query->select('person_2')->where(['person_1' => auth()->user()->id])
            ->from('matches');
        })
        ->get()->toArray();

        foreach($matches as $index => $user_info) {
            if(count($user_info['user_images']) > 0) {
                for($i = 0; $i < count($user_info['user_images']); $i++) {
                    $matches[$index]['user_images'][$i]['image_path'] = url('/uploads/users/'.$user_info['user_images'][$i]['image_name']);
                }
            }
        }
        return response()->json([
            'success' => true,
            'message' => __('messages.matches_found'),
            'data' => $matches
        ]);

    }

    function unmatch($user_id = null) {
        if($user_id == null) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_user_id')
            ];
            return response()->json($res);
        }
        $user = User::find($user_id);
        if(!$user) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_user_id')
            ];
            return response()->json($res);
        }
        $ids = [auth()->user()->id, $user_id];
        $result = Match::whereIn('person_1', $ids)
        ->whereIn('person_2', $ids)->delete();
        if($result) {
            $res = [
                'success' => true,
                'message' => __('messages.unmatched_success')
            ];
            return response()->json($res);
        }   else {
            $res = [
                'success' => false,
                'message' => __('messages.couldnot_unmatched')
            ];
            return response()->json($res);
        }
    }

    function getAllConversations() {
        $sender_id = auth()->user()->id;
        $conversations = Match::with(['user_person1', 'user_person2', 'user_person1.user_images', 'user_person2.user_images'])
            ->withCount('thread_messages')
            ->having('thread_messages_count', '>', 0)
            ->where('person_1', $sender_id)
            ->orWhere('person_2', $sender_id)
            ->get()->toArray();
        // echo "<pre>"; print_r($conversations); exit;
        $data_conversations = [];
        $total_unreads = 0;
        if(count($conversations) > 0) {
            foreach($conversations as $thread) {
                if($thread['user_person1']['id'] == $sender_id) {
                    $receiver_id = $thread['user_person2']['id'];
                    $receiver_name = $thread['user_person2']['name'];
                    $is_online = $thread['user_person2']['is_online'];
                    $imgs = [];
                    foreach($thread['user_person2']['user_images'] as $img) {
                        $imgs[] = url('/uploads/users/'.$img['image_name']);
                    }
                }   else {
                    $receiver_id = $thread['user_person1']['id'];
                    $receiver_name = $thread['user_person1']['name'];
                    $is_online = $thread['user_person1']['is_online'];
                    $imgs = [];
                    foreach($thread['user_person1']['user_images'] as $img) {
                        $imgs[] = url('/uploads/users/'.$img['image_name']);
                    }
                }

                $chat_msg_query = ChatMessage::where('thread_id', $thread['thread_id']);
                $last_msg = $chat_msg_query->orderBy('id', 'DESC')->first();
                $unread = $chat_msg_query->where('is_read',0)->where('user_id','!=',$sender_id)->count();

                // dd($last_msg->toArray());
                $last_text = "";
                $last_image = "";
                if($last_msg) {
                    $last_image = "";
                    if($last_msg->image != "") {
                        $last_image = url('/uploads/users/chats/'.$sender_id.'/'.$receiver_id.'/'.$last_msg->image);
                    }
                    $last_text = $last_msg->message;
                }
                $data_conversations[] = [
                    'conversation_id' => $thread['thread_id'], // match_id
                    'receiver_id' => $receiver_id,
                    'receiver_name' => $receiver_name,
                    'receiver_images' => $imgs,
                    'last_text' => $last_text,
                    'last_image' => $last_image,
                    'is_online' => $is_online,
                    'unread_count'=>$unread
                ];
                $total_unreads  = $total_unreads + $unread;
            }
        }

        /* ** */
        $new_matches = Match::with(['user_person1', 'user_person2', 'user_person1.user_images', 'user_person2.user_images'])
            ->withCount('thread_messages')
            ->having('thread_messages_count', '<', 1)
            ->where('person_1', $sender_id)
            ->orWhere('person_2', $sender_id)
            ->orderBy('id', 'DESC')
            ->get()->toArray();
        // echo "<pre>"; print_r($threads); exit;
        $data_new_matches = [];
        $total_new_matches = count($new_matches);
        if($total_new_matches  > 0) {
            foreach($new_matches as $thread) {
                if($thread['user_person1']['id'] == $sender_id) {
                    $receiver_id = $thread['user_person2']['id'];
                    $receiver_name = $thread['user_person2']['name'];
                    $is_online = $thread['user_person2']['is_online'];
                    $imgs_p = [];
                    foreach($thread['user_person2']['user_images'] as $img) {
                        $imgs_p[] = url('/uploads/users/'.$img['image_name']);
                    }
                }   else {
                    $receiver_id = $thread['user_person1']['id'];
                    $receiver_name = $thread['user_person1']['name'];
                    $is_online = $thread['user_person1']['is_online'];
                    $imgs_p = [];
                    foreach($thread['user_person1']['user_images'] as $img) {
                        $imgs_p[] = url('/uploads/users/'.$img['image_name']);
                    }
                }
                $data_new_matches[] = [
                    'conversation_id' => $thread['thread_id'], //match_id
                    'receiver_id' => $receiver_id,
                    'receiver_name' => $receiver_name,
                    'receiver_images' => $imgs_p,
                    'is_online' => $is_online
                ];
            }
        }


        $total_likes_count = LikeDislike::with(['sender', 'sender.user_images'])
            ->where(['receiver_id' => auth()->user()->id, 'like_dislike' => 1])
            ->where(function($query){
                $query->where(['is_super' => 0])
                      ->orWhere(['is_super' => 1]);
            })
            ->whereNotIn('sender_id',function($query) {
                $query->select('receiver_id')->where(['sender_id' => auth()->user()->id, 'like_dislike' => 1])->from('like_dislike');
            })
            ->count();
        // echo "<pre>"; print_r( $likes_count->toArray() );

        return response()->json([
            'success' => true,
            'message' => __('messages.conversations_list_found'),
            'data' => [
                'data_conversations' => $data_conversations,
                'data_new_matches' => $data_new_matches,
                'total_unreads' => $total_unreads,
                'total_new_matches' => $total_new_matches,
                'total_likes_count' =>$total_likes_count
            ]
        ]);

    }

    function upload_chat_image(Request $request) {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image', //|mimes:jpeg,jpg,png'
            'receiver_id' => 'required|exists:users,id'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.' . $validator->messages()->first())
            ];
            return response()->json($res);
        }
        $sender_id = auth()->user()->id;
        $data = $request->all();
        $file = $request->file('image');

        $full_name = $file->getClientOriginalName();
        $name = time() . '-' . $full_name;
        $path = public_path('/uploads/users/chats/'.$sender_id.'/'.$data['receiver_id']);
        $file_r = $file->move($path, $name);
        $image_url = url('/uploads/users/chats/'.$sender_id.'/'.$data['receiver_id'].'/'.$name);
        $res = [
            'success' => true,
            'message' => __('messages.chat_image_uploaded_success'),
            'data' => [
                'image_name' => $name,
                'image_url' => $image_url
            ]
        ];
        return response()->json($res);
    }

    function save_message(Request $request) {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'is_read' => 'required | boolean'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res);
        }
        $data = $request->all();
        if((!isset($data['message']) || (trim($data['message']) == "")) && !isset($data['image'])) {
            $res = [
                'success' => false,
                'message' => __('messages.either_send_image_or_message')
            ];
            return response()->json($res);
        }
        if($data['receiver_id'] == auth()->user()->id) {
            $res = [
                'success' => false,
                'message' => __('messages.cannot_send_message_to_own_profile')
            ];
            return response()->json($res);
        }

        $sender_id = auth()->user()->id;
        // $thread_data = Match::where('person_1', [$sender_id, $data['receiver_id']])
        // ->orWhere('person_2', [$sender_id, $data['receiver_id']])->first();

        $thread_data = Match::whereIn('person_1', [$sender_id, $data['receiver_id']])
        ->whereIn('person_2', [$sender_id, $data['receiver_id']])->first();

        if(!$thread_data) {
            $res = [
                'success' => false,
                'message' => __('messages.it_is_not_your_match')
            ];
            return response()->json($res);
        }

        $thread = $thread_data->thread_id;
        $chat = new ChatMessage();
        $chat->user_id = $sender_id;
        $chat->thread_id = $thread;
        $chat->is_read = $data['is_read'];
        $chat->message = "";
        if(isset($data['message']) && trim($data['message']) != "") {
            $chat->message = trim($data['message']);
        }
        $image_url = "";
        if(isset($data['image']) && trim($data['image']) != "") {
            $validator = Validator::make($request->all(), [
                'image' => 'required|string' //|mimes:jpeg,jpg,png'
            ]);
            if ($validator->fails()) {
                $res = [
                    'success' => false,
                    'message' => __('messages.'.$validator->messages()->first())
                ];
                return response()->json($res);
            }
            $chat->image = $data['image'];
        }
        if($chat->save()) {

            //send push notification
            if( $chat && ! $chat->is_read ){
                $notifyMe = User::find($data['receiver_id']);
                //check if user has allowed notifications
                if( $notifyMe->app_notification ){

                    //get total unreads
                    $total_unread = 0;
                    $conversations = Match::withCount('unread_thread_messages')
                                            ->having('unread_thread_messages_count','>',0)
                                            ->where('person_1', $sender_id)
                                            ->orWhere('person_2', $sender_id)
                                            ->get();
                    $total_unread = $conversations->reduce(function ($carry, $item){
                        return $carry + $item->unread_thread_messages_count;
                    });

                    //sender image
                    $sender_image =  UserImage::where('user_id', $sender_id)->get();
                    // dd($sender_image);
                    $sender_image = ($sender_image->count() > 0) ? $sender_image->pluck('image_name')[0] : '';

                    // Store notification to DB
                    // $notifyMe->notify(new chatMessageReceived($chat));
                    // push notification
                    $firebase = new FirebaseHelper();
                    $firebase->chatMessageReceived( $chat, $thread_data, $sender_image, $total_unread, $notifyMe->id );
                }
            }

            $res = [
                'success' => true,
                'message' => __('messages.message_saved')
            ];
            return response()->json($res);
        }   else {
            $res = [
                'success' => false,
                'message' => __('messages.could_not_save_message')
            ];
            return response()->json($res);
        }
    }

    function set_show_my_gender($option = null) {
        if($option == null || !in_array($option, [0, 1])) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_option_value')
            ];
            return response()->json($res);
        }
        $user_id = auth()->user()->id;
        $user = User::find($user_id);
        $user->show_my_gender = $option;
        if($user->save()) {
            $res = [
                'success' => true,
                'message' => __('messages.updated_successfully')
            ];
            return response()->json($res);
        }   else {
            $res = [
                'success' => false,
                'message' => __('messages.could_not_update')
            ];
            return response()->json($res);
        }
    }

    function get_chat($receiver_id = null) {
        if($receiver_id == null) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_receiver_id')
            ];
            return response()->json($res);
        }
        $sender_id = auth()->user()->id;

        $thread_data = Match::whereIn('person_1', [$sender_id, $receiver_id])
        ->whereIn('person_2', [$sender_id, $receiver_id])->first();

        if(!$thread_data) {
            $res = [
                'success' => false,
                'message' => __('messages.it_is_not_your_match')
            ];
            return response()->json($res);
        }

        $messages = ChatMessage::where('thread_id', $thread_data->thread_id)->get()->toArray();
        $all_msgs = [];
        foreach($messages as $msg) {
            if(trim($img['image']) == "") {
                $msg['image_url'] = url('/uploads/users/chats/'.$sender_id.'/'.$receiver_id.'/'.$img['image']);
            }   else {
                $msg['image_url'] = "";
            }

            $all_msgs[] = $img;
        }
        $res = [
            'success' => true,
            'message' => __('messages.messages_found'),
            'data' => $messages
        ];
        return response()->json($res);
    }

    function get_who_like_me(Request $request) {
       $users = LikeDislike::with(['sender', 'sender.user_images'])
            ->where('receiver_id' , auth()->user()->id)
            ->where(function($query) {
                $query->where(['like_dislike' => 1, 'is_super' => 0])
                      ->orWhere(['like_dislike' => 1, 'is_super' => 1])
                      ;
            })
            ->whereNotIn('sender_id',function($query) {
                $query->select('receiver_id')->where(['sender_id' => auth()->user()->id, 'like_dislike' => 1])->from('like_dislike');
            })
            ->get()->toArray();

        // dd($users[0] );

        // $users = LikeDislike::with(['sender', 'sender.user_images'])
        //     ->where(['receiver_id' => auth()->user()->id, 'like_dislike' => 1, 'is_super' => 0])
        //     ->whereNotIn('sender_id',function($query) {
        //         $query->select('receiver_id')->where(['sender_id' => auth()->user()->id, 'like_dislike' => 1])->from('like_dislike');
        //     })
        //     ->get()->toArray();


        foreach($users as $index => $user_info) {
            if(count($user_info['sender']['user_images']) > 0) {
                for($i = 0; $i < count($user_info['sender']['user_images']); $i++) {
                    $users[$index]['sender']['user_images'][$i]['image_path'] = url('/uploads/users/'.$user_info['sender']['user_images'][$i]['image_name']);
                }
            }
        }


        $lat1 = auth()->user()->latitude;
        $lon1 = auth()->user()->longitude;

        $all_users = [];
        foreach($users as $user) {
            $lat2 = $user['sender']['latitude'];
            $lon2 = $user['sender']['longitude'];

            if (($lat1 == $lat2) && ($lon1 == $lon2)) {
                $distance = 0;
            }
            else {
                $theta = $lon1 - $lon2;
                $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $distance = ($miles * 1.609344);

            }
            $user['sender']['distance'] = (string) $distance;


            $interests = [];
            if($user['sender']['interests'] != null) {
                $interest_ids = explode(',', $user['sender']['interests']);
                foreach($interest_ids as $int_id) {
                    $language=$request->header('Content-Language');
                    $fields=$this->selectFields($language);
                    $interest = Interest::select($fields)->where('id', $int_id)->first();
                    if($interest) {
                        $interests[] = $interest->toArray();
                    }
                }
            }
            $user['sender']['interests'] = $interests;
            $user['sender']['is_super'] = $user['is_super'];

            $all_users[] = $user['sender'];
        }
        $res = [
            'success' => true,
            'message' => __('messages.likers_found'),
            'data' => $all_users
        ];
        return  response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }

    function get_who_super_like_me(Request $request) {

        $users = LikeDislike::with(['sender', 'sender.user_images'])
                        ->where(['receiver_id' => auth()->user()->id, 'like_dislike' => 1, 'is_super' => 1])

                        ->whereNotIn('sender_id',function($query) {
                            $query->select('receiver_id')->where(['sender_id' => auth()->user()->id, 'like_dislike' => 1])->from('like_dislike');
                        })
                        ->whereNotIn('receiver_id',function($query) {
                            $query->select('reported_id')->where('reported_by', auth()->user()->id)->from('reports');
                        })
                        ->whereNotIn('receiver_id',function($query) {
                            $query->select('reported_by')->where('reported_id', auth()->user()->id)->from('reports');
                        })
                        ->get()->toArray();

        foreach($users as $index => $user_info) {
            if(count($user_info['sender']['user_images']) > 0) {
                for($i = 0; $i < count($user_info['sender']['user_images']); $i++) {
                    $users[$index]['sender']['user_images'][$i]['image_path'] = url('/uploads/users/'.$user_info['sender']['user_images'][$i]['image_name']);
                }
            }
        }
        $all_users = [];
        $lat1 = auth()->user()->latitude;
        $lon1 = auth()->user()->longitude;

        foreach($users as $user) {
            $lat2 = $user['sender']['latitude'];
            $lon2 = $user['sender']['longitude'];

            if (($lat1 == $lat2) && ($lon1 == $lon2)) {
                $distance = 0;
            }
            else {
                $theta = $lon1 - $lon2;
                $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $distance = ($miles * 1.609344);

            }
            $user['sender']['distance'] = (string) $distance;


            $interests = [];
            if($user['sender']['interests'] != null) {
                $interest_ids = explode(',', $user['sender']['interests']);
                foreach($interest_ids as $int_id) {
                    $language=$request->header('Content-Language');
                    $fields=$this->selectFields($language);
                    $interest = Interest::select($fields)->where('id', $int_id)->first();
                    if($interest) {
                        $interests[] = $interest->toArray();
                    }
                }
            }
            $user['sender']['interests'] = $interests;

            $all_users[] = $user['sender'];
        }
        $res = [
            'success' => true,
            'message' => __('messages.super_likers_found'),
            'data' => $all_users
        ];
        return response()->json($res);
    }

    function set_app_notification($val = 1) {
        if($val == null || !in_array($val, [0,1])) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_value')
            ];
            return response()->json($res);
        }
        $user = User::find(auth()->user()->id);
        $user->app_notification = $val;
        if($user->save()) {
            $res = [
                'success' => true,
                'message' => __('messages.notification_setings_updated')
            ];
            return response()->json($res);
        }   else {
            $res = [
                'success' => false,
                'message' => __('messages.notification_setings_not_updated')
            ];
            return response()->json($res);
        }
    }

    function deleteAccount() {
        $user_id = \Auth::user()->id;
        Feedback::where('user_id',$user_id)->delete();
        UserTopPickup::where('user_id',$user_id)->delete();
        UserImage::where('user_id',$user_id)->delete();
        Log::where('user_id',$user_id)->delete();
        ChatMessage::where('user_id',$user_id)->delete();
        DeviceToken::where('user_id',$user_id)->delete();
        SubscriptionReceipts::where('user_id',$user_id)->delete();
        ConsumableFeatures::where('user_id',$user_id)->delete();
        $subscriptionIds = Subscriptions::where('user_id',$user_id)->pluck("id");
        SubscriptionFeaturesStatus::whereIn('subscription_id',$subscriptionIds)->delete();
        LikeDislike::where('sender_id', $user_id)->orWhere('receiver_id', $user_id)->delete();
        Match::with(['thread_messages'])->where('person_1', $user_id)->orWhere('person_2', $user_id)->delete();
        Subscriptions::where('user_id',$user_id)->delete();

        $res = User::where('id', auth()->user()->id)->delete();
        if($res) {
            $res = [
                'success' => true,
                'message' => __('messages.account_deleted_successfully')
            ];
            return response()->json($res);
        }   else {
            $res = [
                'success' => false,
                'message' => __('messages.could_not_delete_account')
            ];
            return response()->json($res);
        }
    }

    function save_interest(Request $request) {
        $validator = Validator::make($request->all(), [
            'interests' => 'required'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res);
        }

        $data = $request->all();
        if(empty($data['interests'])) {
            $res = [
                'success' => false,
                'message' => __('messages.add_atleast_one_interest')
            ];
            return response()->json($res);
        }
        foreach($data['interests'] as $index => $inter) {
            $int = Interest::find($inter);
            if(!$int) {
                unset($data['interests'][$index]);
            }
        }
        $interests = implode(',', $data['interests']);
        $user = User::find(auth()->user()->id);
        $user->interests = $interests;
        if($user->save()) {
            return response()->json(['success' => true, 'message' => __('messages.interest_updated_success')]);
        }   else {
            return response()->json(['success' => false, 'message' => __('messages.action_failed')]);
        }
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

    function all_interests(Request $request) {
          $header_lang = $request->header('Content-Language');
          $fields=$this->selectFields($header_lang);
        //dd($header_lang);
        $interests = Interest::select($fields)->get()->toArray();
        return response()->json(['success' => true, 'message' => __('messages.interest_found'), 'data' => $interests]);
    }

    function sendNotificationAndroid(Request $request)
    {
        //$firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        $SERVER_API_KEY='';
        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        dd($response);
    }

    /**
     * Verify Email with OTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function verifyEmailWithOtp(Request $request)
    {
        $todayDate = date("Y-m-d H:i:s");

        $input = $request->all();

        $messages = [
            'otp.required'=>__('messages.otp_required')
        ];

        $validator = Validator::make($input,[
            'otp'         => 'required',
            'device_token' => 'required',
            'device_type'  => 'required'
        ],$messages);
        if($validator->fails()) {
            $ret = array('success'=>false, 'message'=> $validator->messages()->first());
            return  response()->json($ret,200,[],JSON_NUMERIC_CHECK);
        }

        $user = auth()->user();

        if($user){
            if($user->is_verified=='0'){
                if( $user->otp == $input['otp'] ){
                    if( $user->otp_expiration_time >= $todayDate ){

                        $userDetails= $user;
                        $userDetails->otp=NULL;
                        $userDetails->email_verified_at=$todayDate;
                        $userDetails->is_verified=1;
                        $userDetails->save();

                        if(isset($input['device_token']) && isset($input['device_type'])) {
                            if(trim($input['device_token']) != "" && trim($input['device_type']) != "" && in_array($input['device_type'], [1, 2])) {
                                $already_login = DeviceToken::where(['device_token' => $input['device_token'], 'device_type' => $input['device_type']])->first();
                                if($already_login) {
                                    $device = DeviceToken::find($already_login->id);
                                }   else {
                                    $device = new DeviceToken();
                                }

                                $device->user_id = auth()->user()->id;
                                $device->device_type = $input['device_type'];
                                $device->device_token = $input['device_token'];
                                if(!$device->save()) {
                                    $res = ['success' => false, 'message' => __('messages.device_token_not_saved')];
                                    return response()->json($res);
                                }
                            }
                        }
                        $res = ['success' => true, 'message' => __('messages.email_verified')];
                        return  response()->json($res,200,[],JSON_NUMERIC_CHECK);

                    }else{
                        $message = array('success'=>false,'message'=>__('messages.otp_expired'));
                        return  response()->json($message);
                    }
                }else{
                   $message = array('success'=>false,'message'=>__('messages.invalid_OTP'));
                  return  response()->json($message);
                }
            }else{
                $message = array('success'=>false,'message'=>__('messages.email_already_verified'));
                return  response()->json($message);
            }
        }else{
            $message = array('success'=>false,'message'=>__('messages.no_user_found'));
            return  response()->json($message);
        }
    }


    /**
     * Resend OTP
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
     public function resendOtp(Request $request)
     {
        $input = $request->all();
        $user = auth()->user();

        try{
            if( $user ){
                if( $user->is_verified == '0'){

                    $todayDate=date("Y-m-d H:i:s");
                    $expiryDateTime= date("Y-m-d H:i:s",strtotime("+60 minutes", strtotime($todayDate)));

                    $updateUser = $user;
                    $updateUser->otp = CommonHelpers::generateOtp(6);
                    $updateUser->otp_expiration_time = $expiryDateTime;
                    $updateUser->save();

                    /* send mail with random code for verification.... */
                    Mail::send('email.email_verify_otp', [ 'details' => $user->toArray() ,'msg'=>__('messages.otp_mail')],
                        function ($m) use ($user){
                        // $m->from(env('MAIL_FROM_ADDRESS'),env('YOUR_APP_NAME'));
                        // $m->to($params['email'])->subject('Reset Your Password');
                        $m->from(config('mail.from.address'),config('app.name'));
                        $m->to($user->email)->subject('OTP for account verification');
                    });
                    \Log::info( "Verification Mail sent time: ". \Carbon\Carbon::now() );

                    $res = ['success' => true, 'message' => __('messages.otp_resent_successfully'), 'user' => $user,'otp_expiry_time'=>$expiryDateTime];
                    return  response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
                else {
                    $response = array('success'=>false, 'message'=>__('messages.email_already_verified'));
                    return  response()->json($response);
                }

            }else{
                $response = array('success'=>false, 'message'=>__('messages.no_user_found'));
                return  response()->json($response);
            }
        }
        catch(Exception $e){
            report($e);
            return response()->json(array('success' => false, 'message' => $e->getMessage()));
        }
     }



    /**
     * Update virtual location
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
     public function virtualLocation(Request $request)
     {
        $input = $request->all();
        $user = auth()->user();
        try{
            $user = auth()->user();
            // dd($user);
            if( $user ){

                $interests = [];
                if($user->interests != null) {
                    $interest_ids = explode(',', $user->interests);
                    foreach($interest_ids as $int_id) {
                        $language = $request->header('Content-Language');
                        $fields=$this->selectFields($language);
                        $interest = Interest::select($fields)->where('id', $int_id)->first();
                        if($interest) {
                            $interests[] = $interest->toArray();
                        }
                    }
                }

                $updateUser = $user;
                $updateUser->virtual_latitude = $input['virtual_latitude'];
                $updateUser->virtual_longitude = $input['virtual_longitude'];
                $updateUser->virtual_city = $input['virtual_city'];
                $updateUser->save();

                $user->interests = $interests;

                $res = ['success' => true, 'message' => __('settings.virtual_location_updated'), 'user' => $user];
                return  response()->json($res,200,[],JSON_NUMERIC_CHECK);

            }else{
                $response = array('success'=>false, 'message'=>'User not found. Please register.');
                return  response()->json($response);
            }
        }
        catch(Exception $e){
            report($e);
            return response()->json(array('success' => false, 'message' => $e->getMessage()));
        }
     }


    /**
     * Add feedback
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
     public function addFeedback(Request $request)
     {
        $input = $request->all();
        $user = auth()->user();

        $validator = Validator::make($input,[
            'title'         => 'required',
            'description' => 'required'
        ]);
        if($validator->fails()) {
            $ret = array('success'=>false, 'message'=> $validator->messages()->first());
            return  response()->json($ret,200,[],JSON_NUMERIC_CHECK);
        }

        try{
            $user = auth()->user();
            if( $user ){
                $updateUser = $user;

                $params =   array(
                                'title' => $input['title'],
                                'description' => $input['description'],
                                'user_id' => $user->id
                            );

                $feedback = Feedback::create($params);

                if( $feedback )
                    $res = ['success' => true, 'message' => __('settings.feedback_submitted')];
                else
                    $res = ['success' => false, 'message' => __('errors.something_went_wrong')];

                return  response()->json($res,200,[],JSON_NUMERIC_CHECK);

            }else{
                $response = array('success'=>false, 'message'=>'User not found. Please register.');
                return  response()->json($response);
            }
        }
        catch(Exception $e){
            report($e);
            return response()->json(array('success' => false, 'message' => $e->getMessage()));
        }
     }

    /**
     * Get plans list
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
     public function getAllAvailablePlansList(Request $request)
     {
        $input = $request->all();
        $user = auth()->user();

        $validator = Validator::make($input,[
        ]);
        if($validator->fails()) {
            $ret = array('success'=>false, 'message'=> $validator->messages()->first());
            return  response()->json($ret,200,[],JSON_NUMERIC_CHECK);
        }

        try{
            if( $user ){
                $plans = SubscriptionPlans::select('id','plan_name','product_id')->get();

                if( $plans )
                    $res = ['success' => true, 'message' => __('subscriptions.listed'),'plans'=>$plans];
                else
                    $res = ['success' => false, 'message' => __('errors.something_went_wrong')];

                return  response()->json($res,200,[],JSON_NUMERIC_CHECK);

            }else{
                $response = array('success'=>false, 'message'=>'User not found. Please register.');
                return  response()->json($response);
            }
        }
        catch(Exception $e){
            report($e);
            return response()->json(array('success' => false, 'message' => $e->getMessage()));
        }
     }

    /**
     * Get plans list
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function setMessagesRead(Request $request, ChatMessage $chatmessage)
    {
        $user = auth()->user();

        $messages = [
            'thread_id.required' => __('messages.thread_id_required')
        ];

        $validator = Validator::make($request->all(), [
            'thread_id' => 'required|exists:chat_messages'
        ],$messages);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __($validator->messages()->first())
            ];
            return response()->json($res);
        }

        $data = $request->all();

        try{
            $chat_messages = $chatmessage->where('thread_id',$data['thread_id'])->where( 'user_id' , '!=' , $user->id );

            if( $chat_messages->exists()){
                $read =  $chat_messages->update(['is_read'=>1]);
                return response()->json(array('success' => true, 'message' => __('messages.all_messages_read')));
            }else{
                return response()->json(array('success' => false, 'message' => __('messages.no_chat_found')));
            }
        }
        catch(Exception $e){
            report($e);
            return response()->json( array( 'success' => false, 'message' => $e->getMessage() ),200,[],JSON_NUMERIC_CHECK );
        }

    }


    /**
     * Get total unread messages
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function totalUnread(Request $request, Match $match)
    {
        $authUser = auth()->user();
        try{
            //total unread messages
            $total_unread = 0;
            $conversations = $match->withCount('unread_thread_messages')
                                    ->having('unread_thread_messages_count','>',0)
                                    ->where('person_1', $authUser->id)
                                    ->orWhere('person_2', $authUser->id)
                                    ->get();

            $total_unread = $conversations->reduce(function ($carry, $item){
                return $carry + $item->unread_thread_messages_count;
            });

            return response()->json( array( 'success' => true, 'total_unread_message'=>$total_unread) );
        }
        catch(Exception $e){
            report($e);
            return response()->json( array( 'success' => false, 'message' => $e->getMessage() ),200,[],JSON_NUMERIC_CHECK );
        }
    }

}
