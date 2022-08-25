<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\JsonResponse;
use App\Jobs\UpdateLocation;

use App\Model\User;
use App\Model\DeviceToken;
use App\Model\LikeDislike;
use App\Model\Subscriptions;
use App\Model\SubscriptionFeaturesStatus;
use App\Model\ConsumableFeatures;
use App\Model\Match;
use App\Model\ChatMessage;
use App\Model\Report;
use App\Model\ReportReason;
use App\Model\Interest;
use App\FirebaseHelper;
use Carbon\Carbon;
use App\Model\UserTopPickup;
use Illuminate\Support\Facades\Log;

class SubscriptionFeaturesStatusController extends Controller
{
    protected $jwtAuth;

    /**
     * Initialize
     * @param  Tymon\JWTAuth\JWTAuth $jwtAuth;
     * @return \Illuminate\Http\Response
     */
    public function __construct( JWTAuth $jwtAuth )
    {
        $this->jwtAuth = $jwtAuth;
        $this->middleware('auth:api', ['except' => ['likeDislikeSuperlike','unmatch','boost','getTopPicks','likeDislikeSuperlike_top_picked','resetFeatureCounts']]);
    }

    /**
     * Update user's Like features count
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\SubscriptionFeaturesStatus  $subscriptionFeaturesStatus
     * @return \Illuminate\Http\Response
     */
    public function likeDislikeSuperlike(Request $request, SubscriptionFeaturesStatus $subscriptionFeaturesStatus, Subscriptions $subscription)
    {
        $user = User::with('subscriptions','user_subscription_features','subscription_plan')->whereId(auth()->user()->id)->first();

        /* to do - remove all relationships from User object and replace with model below in queries */
        $subscription_info  = Subscriptions::with('user_features_status','subscription_plan','consumables')->where('subscriptions.user_id',$user->id)->where('is_active',1)->first();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'like_dislike' => 'required|in:0,1',
            'time' => 'required',
            'super' => 'required|in:0,1',
            'feature_type' => 'required|string'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }

        $data = $request->all();
        if($data['user_id'] == $user->id) {
            $res = [
                'success' => false,
                'message' => __('messages.cannot_like_unlike_own_profile')
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }

        /***check plan status and available like and superlike status***/
        //code

        // dd($user->toArray());
        // dump($user->subscriptions->toArray());
        // dd($user->user_subscription_features->toArray());
        $featuresStatusQuery = $subscriptionFeaturesStatus
                             ->where('subscription_id',$user->subscriptions->id)
                             ->where('is_active',1);

        $notificationType  = ""; // like/super_like

        $already_like_dislike = LikeDislike::where(['sender_id' => $user->id, 'receiver_id' => $data['user_id']])->first();

        //check if user already has a like_dislike record
        if($already_like_dislike) {
            $ld_obj = LikeDislike::find($already_like_dislike->id);
            $ld_obj->sender_id = $user->id;
            $ld_obj->receiver_id = $data['user_id'];

            //if request is to superlike
            if( $data['super'] == 1 ){
                //if super like feature is available
                if( $user->user_subscription_features->available_super_likes > 0 ){
                    $ld_obj->is_super = 1;
                    $ld_obj->like_dislike = 1;
                    $savedLike = $ld_obj->save();

                    //deduct superlike from plan quota and update last used time
                    $deductSuperLike = [
                            'available_super_likes' => ($user->user_subscription_features->available_super_likes - 1),
                            'last_super_liked_on' => Carbon::createFromTimestamp($data['time'])
                        ];
                    $featuresStatusQuery->update($deductSuperLike);
                }
                else{
                    $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                                ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                                ->first();
                    $res = ['success' => false,'match'=>$match ? 1 : 0,'message' => __('subscriptions.super_likes_not_available')];
                    $user->refresh();
                    $res['user'] = $user ;
                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
            }

            // if request is to like
            if( $data['like_dislike'] == 1){
                if( $user->user_subscription_features->available_likes > 0 ){
                    $ld_obj->like_dislike = 1;
                    $ld_obj->is_super = 0;
                    $savedLike = $ld_obj->save();

                    $featuresStatus = $featuresStatusQuery->first();

                    //deduct like from plan quota and update last used time
                    $deduct = [
                        'available_likes' => ($featuresStatus->available_likes - 1),
                        'last_liked_on' => Carbon::createFromTimestamp($data['time'])
                    ];
                    $featuresStatusQuery->update($deduct);
                }
                else{
                    $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                            ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                            ->first();

                    $res = ['success' => false,'match'=>$match ? 1 : 0, 'message' => __('subscriptions.likes_not_available')];
                    $user->refresh();
                    $res['user'] = $user ;
                    $subscription_info->refresh();
                    $res['subscription_info'] = $subscription_info;

                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
            }

            //if request is to dislike
            if( $data['like_dislike'] == 0 && $data['super'] == 0){
                $ld_obj->is_super = 0;
                $ld_obj->like_dislike = 0;
                $savedLike = $ld_obj->save();

                $res = ['success' => true, 'message' => __('messages.profile_disliked'), 'match' => 0];
                $user->refresh();
                $res['user'] = $user ;
                $subscription_info->refresh();
                $res['subscription_info'] = $subscription_info;

                return response()->json($res,200,[],JSON_NUMERIC_CHECK);
            }
        }
        //create new like_dislike record
        else {

            $ld_obj = new LikeDislike();
            $ld_obj->sender_id = $user->id;
            $ld_obj->receiver_id = $data['user_id'];

            //if request is to dislike
            if( $data['like_dislike'] == 0 && $data['super'] == 0){

                $ld_obj->like_dislike = 0;
                $ld_obj->is_super = 0;
                $savedLike = $ld_obj->save();

                $res = ['success' => true, 'message' => __('messages.profile_disliked'), 'match' => 0];
                $user->refresh();
                $res['user'] = $user;
                $subscription_info->refresh();
                $res['subscription_info'] = $subscription_info;

                return response()->json($res,200,[],JSON_NUMERIC_CHECK);
            }
            // if request is to like
            if( $data['like_dislike'] == 1){
                if( $user->user_subscription_features->available_likes > 0 ){

                    $ld_obj->like_dislike = 1;
                    $ld_obj->is_super = 0;
                    $savedLike = $ld_obj->save();

                    $featuresStatus = $featuresStatusQuery->first();

                    //deduct like from plan quota and update last used time
                    $deduct = [
                        'available_likes' => ($featuresStatus->available_likes - 1),
                        'last_liked_on' => Carbon::createFromTimestamp($data['time'])
                    ];
                    $featuresStatusQuery->update($deduct);
                    $notificationType = 'like';
                }
                else{
                    $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                            ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                            ->first();

                    $res = ['success' => false,'match'=>$match ? 1 : 0, 'message' => __('subscriptions.likes_not_available')];
                    $user->refresh();
                    $res['user'] = $user ;
                    $subscription_info->refresh();
                    $res['subscription_info'] = $subscription_info;

                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
            }
            //if request is to superlike
            if( $data['super'] == 1 ){
                if( $user->user_subscription_features->available_super_likes > 0 ){
                    $ld_obj->like_dislike = 1;
                    $ld_obj->is_super = 1;
                    $savedLike = $ld_obj->save();

                    //deduct superlike from plan quota and update last used time
                    $deductSuperLike = [
                            'available_super_likes' => ($user->user_subscription_features->available_super_likes - 1),
                            'last_super_liked_on' => Carbon::createFromTimestamp($data['time'])
                        ];
                    $featuresStatusQuery->update($deductSuperLike);
                    $notificationType = 'super_like';
                }
                else{
                    $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                            ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                            ->first();

                    $res = ['success' => false,'match'=>$match ? 1 : 0, 'message' => __('subscriptions.super_likes_not_available')];
                    $user->refresh();
                    $res['user'] = $user;
                    $subscription_info->refresh();
                    $res['subscription_info'] = $subscription_info;

                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }

            }

        }
        $sendNotification = 0;
        if($notificationType!=""){
            $subscriptionDetails  = Subscriptions::with('user_features_status','subscription_plan','consumables')->where('subscriptions.user_id',$data['user_id'])->where('is_active',1)->first();
            if($subscriptionDetails){
                //print_R($subscriptionDetails->toArray());exit;
                if($subscriptionDetails->subscription_plan->plan_name != 'Tundur Free'){
                    $sendNotification = 1;
                }
            }
        }

        //check for match
        if($savedLike){
            //if liked by other user then create a match
            if($data['like_dislike'] == 1 || $data['super'] == 1) {
                //if already liked by other user then create a match
                $row = LikeDislike::where([
                                            'sender_id' => $data['user_id'],
                                            'receiver_id' => $user->id,
                                            'like_dislike' => 1
                                        ])
                                    ->first();
                if($row) {
                    $already_match = Match::whereIn('person_1', [$user->id, $data['user_id']])
                                          ->whereIn('person_2', [$data['user_id'], $user->id])
                                          ->first();
                    if($already_match) {
                        $res = ['success' => true, 'message' => __('messages.already_match'), 'match' => 0];
                    }
                    else {
                        $thread_id = gen_uuid();
                        $mobj = new Match();
                        $mobj->thread_id = $thread_id;
                        $mobj->person_1 = $user->id;
                        $mobj->person_2 = $data['user_id'];
                        if($mobj->save()) {
                            $receiver = User::with(['user_images'])->find($data['user_id'])->toArray();
                            $r_imgs = [];
                            if(count($receiver['user_images']) > 0) {
                                foreach($receiver['user_images'] as $img1) {
                                    $r_imgs[] = url('/uploads/users/'.$img1['image_name']);
                                }
                            }
                            $sendNotification = 0;

                            //send push notification
                            $notifyMe = User::find($data['user_id']);
                            
                            //check if user has allowed notifications
                            if( $notifyMe->app_notification ){
                                $payload['user_id'] = auth()->user()->id;
                                $payload['thread_id'] = $thread_id;
                                // push notification
                                $firebase = new FirebaseHelper();
                                $firebase->matchFound( $payload, $notifyMe->id );
                            }
                            $res = ['success' => true, 'message' => __('messages.its_a_match'), 'match' => 1, 'data' => ['conversation_id' => $thread_id, 'receiver_id' => $data['user_id'], 'receiver_name' => $receiver['name'], 'receiver_images' => $r_imgs, 'last_text' => '', 'last_image' => '', 'is_online' => $receiver['is_online']]];
                        }   else {
                            $res = ['success' => false, 'message' => __('messages.could_not_create_match')];
                        }
                    }
                }   else {
                    $res = ['success' => true, 'message' => __('messages.profile_liked'), 'match' => 0];
                }

            }
            //else if disliked then check if match exists and remove
            else {
                $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                                ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                                ->first();
                if($match) {
                    $result = Match::where('id', $match->id)->delete();
                    // $result_c = ChatMessage::where('thread_id', $match->thread_id);
                }
                $res = ['success' => true, 'message' => __('messages.profile_disliked'), 'match' => 0];
            }
        }
        else {
            $res = ['success' => false, 'message' => __('messages.action_failed')];
        }

        $user->refresh();
        $res['user'] = $user;
        $subscription_info->refresh();
        $res['subscription_info'] = $subscription_info;

        if($sendNotification > 0){

            $notifyMe = User::find($data['user_id']);
            // check if user has allowed notifications
            if( $notifyMe->app_notification ){
                $payload['user_id'] = auth()->user()->id;
                $payload['notificationType'] = $notificationType;
                // push notification
                $firebase = new FirebaseHelper();
                $firebase->likeSuperLikeNotification( $payload, $notifyMe->id );
            }

        }

        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }


    /**
     * Update user's Like features count
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\SubscriptionFeaturesStatus  $subscriptionFeaturesStatus
     * @return \Illuminate\Http\Response
     */
    public function likeDislikeSuperlike_top_picked(Request $request, SubscriptionFeaturesStatus $subscriptionFeaturesStatus, Subscriptions $subscription)
    {
        $user = User::with('subscriptions','user_subscription_features','subscription_plan')->whereId(auth()->user()->id)->first();

        /* to do - remove all relationships from User object and replace with model below in queries */
        $subscription_info  = Subscriptions::with('user_features_status','subscription_plan','consumables')
                                                ->where('subscriptions.user_id',$user->id)
                                                ->where('is_active',1)
                                                ->first();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'like_dislike' => 'required|in:0,1',
            'time' => 'required',
            'super' => 'required|in:0,1',
            'feature_type' => 'required|string'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }

        $data = $request->all();
        if($data['user_id'] == $user->id) {
            $res = [
                'success' => false,
                'message' => __('messages.cannot_like_unlike_own_profile')
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }

        /***check plan status and available like and superlike status***/
        //code

        // dd($user->toArray());
        // dump($user->subscriptions->toArray());
        // dd($user->user_subscription_features->toArray());
        $featuresStatusQuery = $subscriptionFeaturesStatus
                             ->where('subscription_id',$user->subscriptions->id)
                             ->where('is_active',1);

        $already_like_dislike = LikeDislike::where(['sender_id' => $user->id, 'receiver_id' => $data['user_id']])->first();

        //check if user already has a like_dislike record
        if($already_like_dislike) {
            $ld_obj = LikeDislike::find($already_like_dislike->id);
            $ld_obj->sender_id = $user->id;
            $ld_obj->receiver_id = $data['user_id'];

            //if request is to superlike
            if( $data['super'] == 1 ){
                //if super like feature is available
                if( $user->user_subscription_features->available_super_likes > 0 ){
                    $ld_obj->is_super = 1;
                    $ld_obj->like_dislike = 1;
                    $savedLike = $ld_obj->save();

                    //deduct superlike from plan quota and update last used time
                    $deductSuperLike = [
                            'available_super_likes' => ($user->user_subscription_features->available_super_likes - 1),
                            'last_super_liked_on' => Carbon::createFromTimestamp($data['time'])
                        ];
                    $featuresStatusQuery->update($deductSuperLike);

                    // update top picked feature status
                    $updateTopPicked = $this->deductTopPick($subscription_info);
                    if( $updateTopPicked != null ){
                        $featuresStatusQuery->where('subscription_id','=',$subscription_info->id)
                                            ->update($updateTopPicked);
                    }
                }
                else{
                    $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                                ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                                ->first();
                    $user->refresh();
                    $res['user'] = $user ;
                    $res = ['success' => false,'match'=>$match ? 1 : 0,'message' => __('subscriptions.super_likes_not_available')];
                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
            }

            // if request is to like
            if( $data['like_dislike'] == 1){
                if( $user->user_subscription_features->available_likes > 0 ){
                    $ld_obj->like_dislike = 1;
                    $ld_obj->is_super = 0;
                    $savedLike = $ld_obj->save();

                    $featuresStatus = $featuresStatusQuery->first();

                    //deduct like from plan quota and update last used time
                    $deduct = [
                        'available_likes' => ($featuresStatus->available_likes - 1),
                        'last_liked_on' => Carbon::createFromTimestamp($data['time'])
                    ];
                    $featuresStatusQuery->update($deduct);

                    // update top picked feature status
                    $updateTopPicked = $this->deductTopPick($subscription_info);
                    if( $updateTopPicked != null ){
                        $featuresStatusQuery->where('subscription_id','=',$subscription_info->id)
                                            ->update($updateTopPicked);
                    }

                }
                else{
                    $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                            ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                            ->first();

                    $user->refresh();
                    $res['user'] = $user ;
                    $subscription_info->refresh();
                    $res['subscription_info'] = $subscription_info;
                    $res['success'] = false;
                    $res['message'] = __('messages.likes_not_available');
                    $res['match'] = $match ? 1 : 0;

                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
            }

            //if request is to dislike
            if( $data['like_dislike'] == 0 && $data['super'] == 0){
                $ld_obj->is_super = 0;
                $ld_obj->like_dislike = 0;
                $savedLike = $ld_obj->save();

                $updateTopPicked = $this->deductTopPick($subscription_info);
                if( $updateTopPicked != null ){
                    $featuresStatusQuery->where('subscription_id','=',$subscription_info->id)
                                        ->update($updateTopPicked);
                }

                $user->refresh();
                $res['user'] = $user ;
                $subscription_info->refresh();
                $res['subscription_info'] = $subscription_info;
                $res['success'] = true;
                $res['message'] = __('messages.profile_disliked');
                $res['match'] = 0;

                return response()->json($res,200,[],JSON_NUMERIC_CHECK);
            }
        }
        //create new like_dislike record
        else {

            $ld_obj = new LikeDislike();
            $ld_obj->sender_id = $user->id;
            $ld_obj->receiver_id = $data['user_id'];

            //if request is to dislike
            if( $data['like_dislike'] == 0 && $data['super'] == 0){

                $ld_obj->like_dislike = 0;
                $ld_obj->is_super = 0;
                $savedLike = $ld_obj->save();

                $updateTopPicked = $this->deductTopPick($subscription_info);
                if( $updateTopPicked != null ){
                    $featuresStatusQuery->where('subscription_id','=',$subscription_info->id)
                                        ->update($updateTopPicked);
                }

                $user->refresh();
                $res['user'] = $user;
                $subscription_info->refresh();
                $res['subscription_info'] = $subscription_info;
                $res['success'] = true;
                $res['message'] = __('messages.profile_disliked');
                $res['match'] =  0;

                return response()->json($res,200,[],JSON_NUMERIC_CHECK);
            }
            // if request is to like
            if( $data['like_dislike'] == 1){
                if( $user->user_subscription_features->available_likes > 0 ){

                    $ld_obj->like_dislike = 1;
                    $ld_obj->is_super = 0;
                    $savedLike = $ld_obj->save();

                    $featuresStatus = $featuresStatusQuery->first();

                    //deduct like from plan quota and update last used time
                    $deduct = [
                        'available_likes' => ($featuresStatus->available_likes - 1),
                        'last_liked_on' => Carbon::createFromTimestamp($data['time'])
                    ];
                    $featuresStatusQuery->update($deduct);

                    // update top picked feature status
                    $updateTopPicked = $this->deductTopPick($subscription_info);
                    if( $updateTopPicked != null ){
                        $featuresStatusQuery->where('subscription_id','=',$subscription_info->id)
                                            ->update($updateTopPicked);
                    }
                }
                else{
                    $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                            ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                            ->first();

                    $user->refresh();
                    $res['user'] = $user ;
                    $subscription_info->refresh();
                    $res['subscription_info'] = $subscription_info;
                    $res['success'] = false;
                    $res['message'] = __('messages.likes_not_available');
                    $res['match'] =  $match ? 1 : 0;

                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
            }
            //if request is to superlike
            if( $data['super'] == 1 ){
                if( $user->user_subscription_features->available_super_likes > 0 ){
                    $ld_obj->like_dislike = 1;
                    $ld_obj->is_super = 1;
                    $savedLike = $ld_obj->save();

                    //deduct superlike from plan quota and update last used time
                    $deductSuperLike = [
                            'available_super_likes' => ($user->user_subscription_features->available_super_likes - 1),
                            'last_super_liked_on' => Carbon::createFromTimestamp($data['time'])
                        ];
                    $featuresStatusQuery->update($deductSuperLike);

                    // update top picked feature status
                    $updateTopPicked = $this->deductTopPick($subscription_info);
                    if( $updateTopPicked != null ){
                        $featuresStatusQuery->where('subscription_id','=',$subscription_info->id)
                                            ->update($updateTopPicked);
                    }
                }
                else{
                    $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                            ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                            ->first();

                    $user->refresh();
                    $res['user'] = $user;
                    $subscription_info->refresh();
                    $res['subscription_info'] = $subscription_info;
                    $res['success'] = false;
                    $res['message'] = __('messages.super_likes_not_available');
                    $res['match'] =  $match ? 1 : 0;
                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }

            }
        }

        //check for match
        if($savedLike){
            //if liked by other user then create a match
            if($data['like_dislike'] == 1 || $data['super'] == 1) {
                //if already liked by other user then create a match
                $row = LikeDislike::where([
                                            'sender_id' => $data['user_id'],
                                            'receiver_id' => $user->id,
                                            'like_dislike' => 1
                                        ])
                                    ->first();
                if($row) {
                    $already_match = Match::whereIn('person_1', [$user->id, $data['user_id']])
                                          ->whereIn('person_2', [$data['user_id'], $user->id])
                                          ->first();
                    if($already_match) {
                        $res = ['success' => true, 'message' => __('messages.already_match'), 'match' => 0];
                    }
                    else {
                        $thread_id = gen_uuid();
                        $mobj = new Match();
                        $mobj->thread_id = $thread_id;
                        $mobj->person_1 = $user->id;
                        $mobj->person_2 = $data['user_id'];
                        if($mobj->save()) {
                            $receiver = User::with(['user_images'])->find($data['user_id'])->toArray();
                            $r_imgs = [];
                            if(count($receiver['user_images']) > 0) {
                                foreach($receiver['user_images'] as $img1) {
                                    $r_imgs[] = url('/uploads/users/'.$img1['image_name']);
                                }
                            }

                            //send push notification
                            $notifyMe = User::find($data['user_id']);
                            //check if user has allowed notifications
                            if( $notifyMe->app_notification ){
                                $payload['user_id'] = auth()->user()->id;
                                $payload['thread_id'] = $thread_id;
                                // push notification
                                $firebase = new FirebaseHelper();
                                $firebase->matchFound( $payload, $notifyMe->id );
                            }


                            $res = ['success' => true, 'message' => __('messages.its_a_match'), 'match' => 1, 'data' => ['conversation_id' => $thread_id, 'receiver_id' => $data['user_id'], 'receiver_name' => $receiver['name'], 'receiver_images' => $r_imgs, 'last_text' => '', 'last_image' => '', 'is_online' => $receiver['is_online']]];
                        }   else {
                            $res = ['success' => false, 'message' => __('messages.could_not_create_match')];
                        }
                    }
                }   else {
                    $res = ['success' => true, 'message' => __('messages.profile_liked'), 'match' => 0];
                }

            }
            //else if disliked then check if match exists and remove
            else {
                $match = Match::where(['person_1' => $user->id, 'person_2' => $data['user_id']])
                                ->orWhere(['person_1' => $data['user_id'], 'person_2' => $user->id])
                                ->first();
                if($match) {
                    $result = Match::where('id', $match->id)->delete();
                    // $result_c = ChatMessage::where('thread_id', $match->thread_id);
                }
                $res = ['success' => true, 'message' => __('messages.profile_disliked'), 'match' => 0];
            }
        }
        else {
            $res = ['success' => false, 'message' => __('messages.action_failed')];
        }

        $user->refresh();
        $res['user'] = $user;
        $subscription_info->refresh();
        $res['subscription_info'] = $subscription_info;

        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }


    /**
     * Unmatch user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\SubscriptionFeaturesStatus  $subscriptionFeaturesStatus
     * @return \Illuminate\Http\Response
     */
    public function unmatch(Request $request, $user_id = null, SubscriptionFeaturesStatus $subscriptionFeaturesStatus, Subscriptions $subscription)
    {
        $user = User::with('subscriptions','user_subscription_features','subscription_plan')->whereId(auth()->user()->id)->first();
        $data = $request->all();
        // dd($data);
        $validator = Validator::make($data, [
            'feature_type' => 'required|string'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }

        $user = User::find($user_id);
        if(!$user) {
            $res = [
                'success' => false,
                'message' => __('messages.invalid_user_id')
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }
        $ids = [auth()->user()->id, $user_id];
        $result = Match::whereIn('person_1', $ids)
        ->whereIn('person_2', $ids)->delete();
        if($result) {
            $res = [
                'success' => true,
                'message' => __('messages.unmatched_success')
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }   else {
            $res = [
                'success' => false,
                'message' => __('messages.couldnot_unmatched')
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }

        $res['user'] = $user ;
        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }

    /**
     * Boost (30 minutes profile booster)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\SubscriptionFeaturesStatus  $subscriptionFeaturesStatus
     * @return \Illuminate\Http\Response
     */
    public function boost(Request $request, $user_id = null, SubscriptionFeaturesStatus $subscriptionFeaturesStatus, Subscriptions $subscription, ConsumableFeatures $consumableFeatures)
    {
        //get user info
        $user = User::with('user_images')->whereId(auth()->user()->id)->first(); // with('user_images')->

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

        //get user subscription features info
        $subscription_info  = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                                           ->where('subscriptions.user_id',$user->id)
                                           ->where('is_active',1)
                                           ->first();
        $data = $request->all();

        // dd($subscription_info->consumables->where('consumable_type','top_picks')->first()->toArray() );
        $validator = Validator::make($data, [
            'feature_type' => 'required|string',
            'is_consumable' => 'required | boolean'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first())
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }

        // dd($subscription_info);

        //if subscription_info exists (we can remove this condition once database is flushed, as new users will be assigned free plan by default)
        if( $subscription_info != null){
            //check if boost is available
            if($subscription_info->consumables->where('consumable_type','boost')->first()) {
                $consumable_quantity = $subscription_info->consumables->where('consumable_type','boost')->first()->consumable_quantity;
            }   else {
                $consumable_quantity = 0;
            }
            if( $subscription_info->user_features_status->available_boost > 0 ||  $consumable_quantity > 0 ){
                //check last boost use time
                if( $subscription_info->user_features_status->last_boosted_on != null ){
                    $lastBoostedOn = new Carbon($subscription_info->user_features_status->last_boosted_on);

                    // dd( $lastBoostedOn->diffInHours(Carbon::now(), false));

                    // if more than 24 hours since last used or is consumable request
                    if( $lastBoostedOn->diffInHours(Carbon::now(), false) >= 24 || $data['is_consumable'] == true   ){

                        $update ['last_boosted_on'] = Carbon::now();
                        //if consumable request then deduct from there else from subscription
                        if( $data['is_consumable'] ){
                            $consumableBoostQuantity = 0;
                            $consumableBoostQty = $subscription_info->consumables->where('consumable_type','boost')->first();
                            if($consumableBoostQty) {
                                $consumableBoostQuantity = $consumableBoostQty->consumable_quantity;
                            }
                            $consumableBoost = $consumableFeatures->where('user_id',$user->id)
                                                                  ->where('consumable_type','boost')
                                                                  ->update( [ 'consumable_quantity'=> $consumableBoostQuantity - 1 ] );

                        }else{
                            $update['available_boost'] = (int)$subscription_info->user_features_status->available_boost - 1;
                            $update['boost_reset_on'] = Carbon::now()->addDay();
                        }

                        //update boost feature status
                        $subscriptionFeaturesStatus->where('subscription_id','=',$subscription_info->id)
                                                   ->update($update);


                        $user->refresh();
                        $user->interests = $interests;
                        $res['user'] = $user;
                        $subscription_info->refresh();
                        $res['subscription_info'] = $subscription_info;
                        $res['message'] = __('subscriptions.profile_boosted');
                        $res['success'] = true;
                        return response()->json($res,200,[],JSON_NUMERIC_CHECK);

                    }
                    else{
                        //No boost available
                        $user->refresh();
                        $user->interests = $interests;
                        $res['user'] = $user;
                        $subscription_info->refresh();
                        $res['subscription_info'] = $subscription_info;
                        $res['message'] = __('subscriptions.boost_not_available');
                        $res['success'] = true;
                        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                    }
                }
                //boosting first time
                else{

                    $update ['last_boosted_on'] = Carbon::now();
                    //if consumable request then deduct from there else from subscription
                    if( $data['is_consumable'] ){
                        $consumableBoostQuantity = 0;
                        $consumableBoostQty = $subscription_info->consumables->where('consumable_type','boost')->first();
                        if($consumableBoostQty) {
                            $consumableBoostQuantity = $consumableBoostQty->consumable_quantity;
                        }
                        $consumableBoost = $consumableFeatures->where('user_id',$user->id)
                                                                ->where('consumable_type','boost')
                                                                ->update( [ 'consumable_quantity'=> $consumableBoostQuantity - 1 ] );


                    }else{
                        $update['available_boost'] = (int)$subscription_info->user_features_status->available_boost - 1;
                        $update['boost_reset_on'] = Carbon::now()->addDay();
                    }


                    //update boost feature status
                    $subscriptionFeaturesStatus->where('subscription_id','=',$subscription_info->id)
                                                ->update($update);

                    $user->refresh();
                    $user->interests = $interests;
                    $res['user'] = $user;
                    $subscription_info->refresh();
                    $res['subscription_info'] = $subscription_info;
                    $res['message'] = __('subscriptions.profile_boosted');
                    $res['success'] = true;
                    return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                }
            }
            else{
                //No boost available
                $user->refresh();
                $user->interests = $interests;
                $res['user'] = $user;
                $subscription_info->refresh();
                $res['subscription_info'] = $subscription_info;
                $res['message'] = __('subscriptions.boost_not_available');
                $res['success'] = true;
                return response()->json($res,200,[],JSON_NUMERIC_CHECK);
            }
        }
        else{
            //No boost available
            $user->refresh();
            $user->interests = $interests;
            $res['user'] = $user;
            $subscription_info->refresh();
            $res['subscription_info'] = $subscription_info;
            $res['message'] = __('subscriptions.no_subscription_found');
            $res['success'] = true;
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }
    }



    /**
     * Top picks (most liked profiles in my area)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\SubscriptionFeaturesStatus  $subscriptionFeaturesStatus
     * @return \Illuminate\Http\Response
     */
    public function getTopPicks(Request $request, $user_id = null, SubscriptionFeaturesStatus $subscriptionFeaturesStatus, Subscriptions $subscription)
    {
        //get user info
        $user = User::whereId(auth()->user()->id)->first();
        $todayDate = date('Y-m-d');
        $header_lang = $request->header('Content-Language');
        $pickUpUserExist = 0;
        $pickUpUsers = array();
        $userTopPickUp = UserTopPickup::where('user_id',$user->id)->where('pickup_date',$todayDate)->first();
        $langText = "reason_text_".$header_lang;
        $reasons = ReportReason::select("id", $langText." as reason_text")->get();
        if($userTopPickUp){
            if($userTopPickUp->pick_up_users!=""){
                $pickUpUsers = explode(',', $userTopPickUp->pick_up_users);
                $pickUpUserExist = 1;
            }else{
                $res = ['success' => false, 'message' => __('messages.no_user_found'), 'reasons' => $reasons->toArray()];
                return response()->json($res,200,[],JSON_NUMERIC_CHECK);
            }
        }

        //get user subscription features info
        //$subscription_info  = Subscriptions::with('user_features_status','subscription_plan','consumables')->where('subscriptions.user_id',$user->id)->where('is_active',1)->first();

        $header_lang = $request->header('Content-Language');
        //dd($header_lang);

        $data = $request->all();

        // dd($data);
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
            'distance' => 'required',
            'feature_type' => 'required|string'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => __('messages.'.$validator->messages()->first()),
                'reasons' => $reasons->toArray()
            ];
            return response()->json($res,200,[],JSON_NUMERIC_CHECK);
        }

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
        if($pickUpUserExist=='0'){
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
            ->limit(20)
            ->get()->toArray();

        }else{
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
            ->having('distance', '<=', $data['distance'])
            ->having('age', '<=', $data['end_age'])
            ->having('age', '>=', $data['start_age'])
            ->whereIn('id', $pickUpUsers)
            ->orderBy('distance','ASC')
            ->get()
            ->toArray();

        }

        // dd($searchResult[0]);

        $pick_up_users = "";
        foreach($searchResult as $index => $user_info) {
            if($pickUpUserExist=='0'){
                $pick_up_users.=$user_info['id'].',';
            }

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

            $searchResult[$index]['interests'] = $interests;

            //count number of likes for user
            $userLikedCount = LikeDislike::where('receiver_id',$user_info['id'])->count('like_dislike');
            $searchResult[$index]['likedCount'] = $userLikedCount;
        }

        if($pickUpUserExist=='0'){
            $pick_up_users = trim(rtrim($pick_up_users,","));
            UserTopPickup::updateOrCreate(
                    ["user_id" => auth()->user()->id],['user_id' => auth()->user()->id,'pick_up_users' => $pick_up_users, 'pickup_date' => $todayDate]);
        }

        //sort by most liked
        $likedCount = array_column($searchResult, 'likedCount');
        array_multisort($likedCount, SORT_DESC, $searchResult);

        $current_user = $current_user->load('subscriptions','user_subscription_features','subscription_plan');

        if($searchResult) {
            $res = ['success' => true, 'message' => __('messages.users_found'), 'data' => $searchResult, 'reasons' => $reasons->toArray(),'user'=>$current_user];
        }   else {
            $res = ['success' => false, 'message' => __('messages.no_user_found'), 'reasons' => $reasons->toArray()];
        }
        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }


    private function deductTopPick( $subscription_info )
    {

        if( $subscription_info != null ){
            //check if top_pick is available
            if( $subscription_info->user_features_status->available_top_picked >= 1 ){
                // dd($subscription_info->user_features_status->toArray());
                //check last boost use time
                // $lastTopPickedOn = new Carbon($subscription_info->user_features_status->last_top_picked_on);

                $update = [
                    'available_top_picked' =>(int)$subscription_info->user_features_status->available_top_picked - 1,
                    'visible_top_picks' =>(int)$subscription_info->user_features_status->visible_top_picks - 1,
                    'last_top_picked_on' =>Carbon::now(),
                ];

                if( $subscription_info->user_features_status->available_top_picked - 1 == 0){
                    $update['top_picked_reset_on'] = Carbon::now()->addDay();
                }

                return $update;
            }

        }else{
            return null;
        }
    }


    private function selectFields($language)
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
     * Reset feature counts if required
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function resetFeatureCounts(Request $request, Match $match)
    {
        try{
            //get user info
            $user = User::whereId(auth()->user()->id)->first();

            //get user subscription features info
            $subscription_info  = Subscriptions::with('user_features_status','subscription_plan','consumables')
                                            ->where('subscriptions.user_id',$user->id)
                                            ->where('is_active',1)
                                            ->first();

            // dd($subscription_info->toArray());

            if(  $subscription_info->user_features_status ){

                $featuresStatus = $subscription_info->user_features_status;
                $plan = $subscription_info->subscription_plan;

                $reset =[
                    'boost'=>0,
                    'likes'=>0,
                    'super_likes'=>0,
                    'top_picks'=>0,
                ];

                // dd( 'PLAN' , $plan->toArray(), 'FEATURES' ,$featuresStatus->toArray());

                //reset like count
                if( $featuresStatus->likes_reset_on != null ){
                    $likesResetOn= new Carbon($featuresStatus->likes_reset_on);
                    // dd($likeLastUsedOn,Carbon::now(), $likeLastUsedOn->diffInHours(Carbon::now(), false)  );
                    // dd($likeLastUsedOn->diffInMonths(Carbon::now(), false) >= 1);
                    if( $likesResetOn->lessThan(Carbon::now()) ){

                        $reset['likes'] = SubscriptionFeaturesStatus::where('subscription_id',$featuresStatus->subscription_id)
                                                ->update([
                                                        // 'likes_reset_on' => $likesResetOn->addDay(),
                                                        'likes_reset_on' => Carbon::now()->addDay(),
                                                        'available_likes' => 100
                                                ]);
                    }
                }

                //reset super like count
                if( $featuresStatus->super_likes_reset_on != null ){
                    $superLikesResetOn= new Carbon($featuresStatus->super_likes_reset_on);
                    // dd($likeLastUsedOn,Carbon::now(), $likeLastUsedOn->diffInHours(Carbon::now(), false)  );
                    // dd($likeLastUsedOn->diffInMonths(Carbon::now(), false) >= 1);
                    if( $superLikesResetOn->lessThan(Carbon::now()) ){

                        $reset['super_likes'] = SubscriptionFeaturesStatus::where('subscription_id',$featuresStatus->subscription_id)
                                                ->update([
                                                        // 'super_likes_reset_on' => $superLikesResetOn->addDay(),
                                                        'super_likes_reset_on' => Carbon::now()->addDay(),
                                                        'available_super_likes' => $plan->super_likes_count
                                                ]);
                    }
                }

                //reset boost count
                if( $featuresStatus->boost_reset_on != null ){
                    $boostResetOn= new Carbon($featuresStatus->boost_reset_on);
                    // dd($likeLastUsedOn,Carbon::now(), $likeLastUsedOn->diffInHours(Carbon::now(), false)  );
                    // dd($likeLastUsedOn->diffInMonths(Carbon::now(), false) >= 1);
                    if( $boostResetOn->lessThan(Carbon::now()) ){

                        $reset['boost'] = SubscriptionFeaturesStatus::where('subscription_id',$featuresStatus->subscription_id)
                                                ->update([
                                                        // 'boost_reset_on' => $boostResetOn->addMonth(),
                                                        'boost_reset_on' => Carbon::now()->addMonth(),
                                                        'available_boost' => $plan->boost_count
                                                ]);
                    }
                }

                //reset top picks count
                if( $featuresStatus->top_picked_reset_on != null ){
                    $topPicksResetOn = new Carbon($featuresStatus->top_picked_reset_on);
                    // dd($likeLastUsedOn,Carbon::now(), $likeLastUsedOn->diffInHours(Carbon::now(), false)  );
                    // dd($likeLastUsedOn->diffInMonths(Carbon::now(), false) >= 1);
                    if( $topPicksResetOn->lessThan(Carbon::now()) ){

                        $reset['top_picks'] = SubscriptionFeaturesStatus::where('subscription_id',$featuresStatus->subscription_id)
                                                ->update([
                                                        // 'top_picked_reset_on' => $topPicksResetOn->addDay(),
                                                        'top_picked_reset_on' => Carbon::now()->addDay(),
                                                        'available_top_picked' => $plan->boost_count
                                                ]);
                    }
                }

                return response()->json( array( 'success' => true,'message'=>'Features reset.','features_resetted'=>$reset) );
            }
            return response()->json( array( 'success' => false,'message'=>'No resettable features found') );
        }
        catch(\Exception $e){
            report($e);
            return response()->json( array( 'success' => false, 'message' => $e->getMessage() ),200,[],JSON_NUMERIC_CHECK );
        }
    }



}
