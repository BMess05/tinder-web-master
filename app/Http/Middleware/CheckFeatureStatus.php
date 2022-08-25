<?php

namespace App\Http\Middleware;

use Closure;
use App\Model\User;
use App\Model\Subscriptions;
use App\Model\Match;
use Carbon\Carbon;

class CheckFeatureStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = User::with('subscriptions','user_subscription_features','subscription_plan')->findOrFail(auth()->user()->id);

        //get user subscription features info
        $subscription_info  = Subscriptions::with('user_features_status','subscription_plan','consumables')
                                           ->where('subscriptions.user_id',$user->id)
                                           ->where('is_active',1)
                                           ->first();
                                           
        $currentSubscription = $user->subscriptions;

        $res = ['success' => false, 'user'=>$user];

        // dd($user->user_subscription_features->last_super_liked_on->diffInDays(Carbon::now()));
        // dd( $user->user_subscription_features, $user->subscription_plan );

        /**
         * Check if current subscription is active
         * Expiry is greater than current time
         */
        if( $currentSubscription && $currentSubscription->is_active  && $currentSubscription->expires_date > Carbon::now() ){

            $requestData = $request->all();

            //check if requested feature action is available for user
            if( $requestData['feature_type'] == 'like' ){
                if( $requestData['super'] == 0 ){
                    if( $subscription_info->user_features_status->available_likes > 0 ){
                        return $next($request);
                    }else{
                        $match = Match::whereIn('person_1', [$user->id, $requestData['user_id']])
                                          ->whereIn('person_2', [$requestData['user_id'], $user->id])
                                          ->first();
                        $res['match'] = $match ? 1 : 0;
                        $res['message'] = __('subscriptions.likes_not_available');
                        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                    }
                }
                else{

                    $today = $subscription_info->user_features_status->last_super_liked_on->diffInDays(Carbon::now());

                    if( $subscription_info->user_features_status->available_super_likes > 0 && $today > 0 ){
                        return $next($request);
                    }else{
                        $match = Match::whereIn('person_1', [$user->id, $requestData['user_id']])
                                          ->whereIn('person_2', [$requestData['user_id'], $user->id])
                                          ->first();
                        $res['match'] = $match ? 1 : 0;
                        $res['message'] = __('subscriptions.super_likes_not_available');
                        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
                    }
                }
            }

            //check if requested feature action is available for user
            if( $requestData['feature_type'] == 'boost' ){
               //move feature validation here from Api\SubscriptionFeaturesStatusController@boost
            }
        }

        return $next($request);
    }
}
