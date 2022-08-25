<?php

namespace App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Model\User;
use App\Model\Subscriptions;
use App\Model\SubscriptionFeaturesStatus;

use Aws\S3\Exception\S3Exception;
use Auth, File, URL, Exception, Log;
use Carbon\Carbon;

class CommonHelpers
{
    /**
     * Method to generate random OTP
     *
     * @param string $length OTP length
     *
     * @return string Returns OTP
     */
    public static function generateOtp($length = 4)
    {
        $characters = '123456789';
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

    /**
     * Update feature count
     *
     * @return object
     */
    public static function updateFeatureCount()
    {
        //get logged in user info
        $user = User::with('subscriptions')->findOrFail(auth()->user()->id);

        //get logged in user features info
        $features = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)
            ->where('is_active', 1);

        $featuresStatusQuery = $features->first();

        // dd($featuresStatusQuery->first()->toArray());
        if ($featuresStatusQuery) :

            if ($featuresStatusQuery->is_active) {

                //check like status
                //if reset time is over then refill nad update reset time
                $diff = Carbon::now()->diffInMinutes($featuresStatusQuery->likes_reset_on, false);
                // dd($diff);
                if ($diff < 0) {
                    $features->update(['available_likes' => config('tundur.subscription_plans')[1]['features']['available_likes'], 'likes_reset_on' => Carbon::now()->addMinutes(5)]);
                }

                //check super_like status
                //if reset time is over then refill nad update reset time
                $diff = Carbon::now()->diffInMinutes($featuresStatusQuery->super_likes_reset_on, false);
                // dd($diff);
                if ($diff < 0) {
                    $features->update(['available_super_likes' => config('tundur.subscription_plans')[1]['features']['available_super_likes'], 'super_likes_reset_on' => Carbon::now()->addMinutes(5)]);
                }
            }

        endif;
    }
}
