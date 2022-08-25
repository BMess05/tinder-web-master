<?php

namespace App\Listeners;

use App\Events\AssignDefaultPlan;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use App\Model\SubscriptionPlans;
use App\Model\Subscriptions;
use App\Model\SubscriptionFeaturesStatus;
use Carbon\Carbon;

class UserRegistered
{
    protected $user, $subscriptions, $data;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct( Subscriptions $subscriptions )
    {
        $this->subscriptions = $subscriptions;
    }

    /**
     * Handle the event.
     *
     * @param  AssignDefaultPlan  $event
     * @return void
     */
    public function handle(AssignDefaultPlan $event)
    {
        $data['user_id'] = $event->user->id;
        $data['platform'] = $event->platform;
        $data['plan_id'] = $event->plan_id;
        $data['product_id'] = $event->product_id;
        $data['transaction_id'] = $event->transaction_id;

        try{

            //get free plan features
            $defaultSubscription = SubscriptionPlans::where('id',1)->first();

            $subscription = $this->subscriptions->create($data); //create subscription record
            $featuresData =  [ 'subscription_id'=>$subscription->id ];

            $featuresData['available_boost'] = $defaultSubscription->boost_count;
            $featuresData['boost_reset_on'] = Carbon::now()->addDays($defaultSubscription->boost_duration);

            $featuresData['available_likes'] = $defaultSubscription->likes_count;
            $featuresData['likes_reset_on'] = Carbon::now()->addDays($defaultSubscription->likes_duration);

            $featuresData['available_super_likes'] = $defaultSubscription->super_likes_count;
            $featuresData['super_likes_reset_on'] = Carbon::now()->addDays($defaultSubscription->super_likes_duration);

            $featuresData['available_top_picked'] = $defaultSubscription->top_picks_count;
            $featuresData['visible_top_picks'] = $defaultSubscription->top_picks_visible;
            $featuresData['top_picked_reset_on'] = Carbon::now()->addDays($defaultSubscription->top_picks_duration);

            $features = SubscriptionFeaturesStatus::create($featuresData); //create subscription features status record
        }
        catch(Exception $e){
            report($e);
            \Log::debug( "UserRegistered event error message : " . $e->message()  );
        }

        return true;
    }
}
