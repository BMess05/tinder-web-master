<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

use App\Model\User;
use App\Model\SubscriptionPlans;
use App\Model\Subscriptions;
use App\Model\SubscriptionFeaturesStatus;
use App\Model\SubscriptionReceipts;

use App\CommonHelpers;
use Carbon\Carbon;

use App\Events\AssignDefaultPlan;

use App\Model\Settings;

class SubscriptionsController extends Controller
{
    /**
     * Verify receipt and add a subscription
     *
     * @return \Illuminate\Http\Response
     */
    public function verifyReceiptAddSubscription(Request $request, Subscriptions $subscription, SubscriptionReceipts $receipts)
    {
        $user = User::with('subscriptions', 'subscription_receipt')->whereId(auth()->user()->id)->first();

        $input = $request->all();

        $validator = Validator::make($input, [
            'receipt-data' => ['required', 'string'],
            'plan_id' => 'required|exists:subscription_plans,id',
            'platform' => 'required',
            'apple_id' => 'required',
        ]);
        if ($validator->fails()) {
            $ret = array('success' => 0, 'message' => $validator->messages()->first());
            return response()->json($ret);
        }

        if ($user) {
            try {

                $response = Http::withHeaders([
                    'Accept' => 'application/json'
                ])->post(config('tundur.APPLE_VERIFY_RECEIPT_URL'), [  //change from sandbox to buy for production
                    'receipt-data' => $input['receipt-data'],
                    'password' => config('tundur.apple_password'),
                    "exclude-old-transactions" => "true"
                ]);

                $response = json_decode($response->body());

                // dd($response->toArray() );
                // dd($response->latest_receipt_info[0],$response->latest_receipt_info[1] ,$response->latest_receipt_info[2] );

                $latest_receipt = (array) $response->latest_receipt_info[count($response->latest_receipt_info) - 1];
                // dump($latest_receipt);

                //check if already any subscription with same transaction id exists
                $check = $subscription;

                if ($check->where([
                    'user_id' => $user->id,
                    'transaction_id' => $latest_receipt['transaction_id'],
                    'original_transaction_id' => $latest_receipt['original_transaction_id']
                ])
                    ->exists()
                ) {
                    return response()->json(array('success' => 0, 'message' => __('subscriptions.subscription_already_exists')), 200, [], JSON_NUMERIC_CHECK);
                }

                // $input['purchase_date'] = Carbon::createFromTimestamp( $latest_receipt['purchase_date_ms'] )->toDateTimeString();
                $input['purchase_date'] = Carbon::createFromTimestampMs($latest_receipt['purchase_date_ms'])->format('Y-m-d H:i:s');
                $input['expires_date'] = Carbon::createFromTimestampMs($latest_receipt['expires_date_ms'])->format('Y-m-d H:i:s');
                $input['original_purchase_date'] = Carbon::createFromTimestampMs($latest_receipt['original_purchase_date_ms'])->format('Y-m-d H:i:s');

                $input['original_transaction_id'] = $latest_receipt['original_transaction_id'];
                $input['transaction_id'] = $latest_receipt['transaction_id'];
                $input['product_id'] = $latest_receipt['product_id'];
                $input['quantity'] = $latest_receipt['quantity'];
                $input['is_trial_period'] = $latest_receipt['is_trial_period'] == 'true' ? '1' : '0';
                $input['is_in_intro_offer_period'] = $latest_receipt['is_in_intro_offer_period'] == 'true' ? '1' : '0';
                $input['web_order_line_item_id'] = $latest_receipt['web_order_line_item_id'];
                $input['subscription_group_identifier'] = $latest_receipt['subscription_group_identifier'];


                $input['plan_id'] = $input['plan_id'];
                $input['platform'] = $input['platform'];
                $input['apple_id'] = $input['apple_id'];

                $input['user_id'] = $user->id;

                // unset($input['receipt-data']);

                // dump($response->latest_receipt_info[0]->purchase_date);
                // dd($input);

                $subscription = $subscription->create($input); //create subscription record

                if ($subscription) {
                    $plan = SubscriptionPlans::where('id', $input['plan_id'])->first();
                    $featuresData = [
                        'subscription_id' => $subscription->id,
                        'available_boost' => $plan->boost_count,
                        'boost_reset_on' => Carbon::now()->addMonth(),
                        'available_likes' => $plan->unlimited_likes ? '99999999' : '100', //unlimited or 100
                        'available_super_likes' => $plan->super_likes_count,
                        'super_likes_reset_on' => Carbon::now()->addDay(),
                        'available_last_likes' => $plan->last_likes,
                        'available_top_picked' => $plan->top_picks_count,
                        'visible_top_picks' => $plan->top_picks_visible,
                        'top_picked_reset_on' => Carbon::now()->addDay(),
                        'rewind' => $plan->unlimited_rewinds,
                    ];

                    // dd($featuresData);
                    $previous_subscription_id = $user->subscriptions->id;

                    $features = SubscriptionFeaturesStatus::create($featuresData); //create subscription features status record
                    if ($features) {
                        //deactivate previous subscription if any
                        if ($user->subscriptions) {
                            $deactivatePlan = Subscriptions::where('id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateFeatures = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                        }

                        //store receipt data
                        $receipt = $receipts->create([
                            'receipt_data' => $input['receipt-data'],
                            'user_id' => $user->id,
                            'subscription_id' => $subscription->id
                        ]);

                        return response()->json(array('success' => 1, 'data' => $subscription->load('user_features_status', 'subscription_plan'), 'message' => __('subscriptions.subscribed_to_plan')), 200, [], JSON_NUMERIC_CHECK);
                    } else {
                        return response()->json(array('success' => 0, 'message' => __('errors.something_went_wrong')), 200, [], JSON_NUMERIC_CHECK);
                    }
                } else {
                    return response()->json(array('success' => 0, 'message' => __('errors.something_went_wrong')), 200, [], JSON_NUMERIC_CHECK);
                }
            } catch (\Exception $e) {
                $message = array('success' => 0, 'message' => $e->getMessage());
                return response()->json($message);
            }
        } else {
            $message = array('success' => 0, 'message' => __('errors.user_not_found'));
            return response()->json($message);
        }
    }



    /**
     * Check current subscription status
     *
     * @return \Illuminate\Http\Response
     */
    public function checkCurrentSubscriptionStatus(Request $request, Subscriptions $subscription, SubscriptionReceipts $receipts)
    {

        $user = User::with('subscriptions', 'subscription_receipt', 'subscription_plan')->whereId(auth()->user()->id)->first();

        $input = $request->all();

        $validator = Validator::make($input, [
            'platform' => 'required',
            'apple_id' => 'required',
        ]);
        if ($validator->fails()) {
            $ret = array('success' => 0, 'message' => $validator->messages()->first());
            return response()->json($ret);
        }

        // dd($user->subscriptions->toArray());

        if ($user) {
            try {
                // dd($user->subscriptions->product_id);
                //if default free plan
                if ($user->subscriptions->product_id == false) {
                    /****Get available plan and available features of user****/
                    $activeSubscription = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                        ->where('subscriptions.user_id', $user->id)
                        ->where('is_active', 1)
                        ->first();
                    /*********************************************************/

                    return response()->json(array('success' => 1, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_valid')), 200, [], JSON_NUMERIC_CHECK);
                }

                $response = Http::withHeaders([
                    'Accept' => 'application/json'
                ])->post(config('tundur.APPLE_VERIFY_RECEIPT_URL'), [  //change from sandbox to buy for production
                    'receipt-data' => $user->subscription_receipt->receipt_data,
                    'password' => config('tundur.apple_password'),
                    "exclude-old-transactions" => "true"
                ]);

                $response = json_decode($response->body());

                // $latest_receipt = (array) $response->latest_receipt_info[0];
                $latest_receipt = (array) $response->latest_receipt_info[count($response->latest_receipt_info) - 1];
                // dd($latest_receipt);

                //check if latest receipt has same subscription info
                $sameReceiptAndTransaction = false;
                if (($user->subscriptions->transaction_id == $latest_receipt['transaction_id']) &&  ($user->subscriptions->original_transaction_id == $latest_receipt['original_transaction_id'])) {
                    $sameReceiptAndTransaction = true;
                    //
                }
                // dd($sameReceiptAndTransaction);
                //check if subscription expired
                if ($sameReceiptAndTransaction) {
                    // $expired = false;
                    if (($user->subscriptions->expires_date)->lessThan(Carbon::now())) {

                        //deactivate previous subscription if any
                        if ($user->subscriptions) {
                            $deactivatePlan = Subscriptions::where('id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateFeatures = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateReceipt = SubscriptionFeaturesStatus::where('subscription_id', $user->subscription_receipt->id)->where('is_active', 1)->update(['is_active' => 0]);
                        }

                        //trigger AssignDefaultPlan event
                        $eventData = ['platform' => $input['platform'], 'plan_id' => 1, 'transaction_id' => 0, 'product_id' => '0'];
                        event(new AssignDefaultPlan($user, $eventData));


                        /****Get available plan and available features of user****/
                        $activeSubscription  = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                            ->where('subscriptions.user_id', $user->id)
                            ->where('is_active', 1)
                            ->first();
                        /*********************************************************/


                        return response()->json(array('success' => 0, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_expired')), 200, [], JSON_NUMERIC_CHECK);
                    } else {

                        /****Get available plan and available features of user****/
                        $activeSubscription  = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                            ->where('subscriptions.user_id', $user->id)
                            ->where('is_active', 1)
                            ->first();
                        /*********************************************************/

                        return response()->json(array('success' => 1, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_valid')), 200, [], JSON_NUMERIC_CHECK);
                    }
                }
                //if latest_receipt has new subscription info
                else {
                    //check if this subscription is expired
                    if (Carbon::createFromTimestampMs($latest_receipt['expires_date_ms'])->lessThan(Carbon::now())) {

                        //deactivate previous subscription if any
                        if ($user->subscriptions) {
                            $deactivatePlan = Subscriptions::where('id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateFeatures = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateReceipt = SubscriptionFeaturesStatus::where('subscription_id', $user->subscription_receipt->id)->where('is_active', 1)->update(['is_active' => 0]);
                        }

                        //trigger AssignDefaultPlan event
                        $eventData = ['platform' => $input['platform'], 'plan_id' => 1, 'transaction_id' => 0, 'product_id' => '0'];
                        event(new AssignDefaultPlan($user, $eventData));


                        /****Get available plan and available features of user****/
                        $activeSubscription  = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                            ->where('subscriptions.user_id', $user->id)
                            ->where('is_active', 1)
                            ->first();
                        /*********************************************************/

                        return response()->json(array('success' => 0, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_expired')), 200, [], JSON_NUMERIC_CHECK);
                    }
                    //if not than add new subscription record and deactivate previous
                    else {

                        $input['purchase_date'] = Carbon::createFromTimestampMs($latest_receipt['purchase_date_ms'])->format('Y-m-d H:i:s');
                        $input['expires_date'] = Carbon::createFromTimestampMs($latest_receipt['expires_date_ms'])->format('Y-m-d H:i:s');
                        $input['original_purchase_date'] = Carbon::createFromTimestampMs($latest_receipt['original_purchase_date_ms'])->format('Y-m-d H:i:s');

                        $input['original_transaction_id'] = $latest_receipt['original_transaction_id'];
                        $input['transaction_id'] = $latest_receipt['transaction_id'];
                        $input['product_id'] = $latest_receipt['product_id'];
                        $input['quantity'] = $latest_receipt['quantity'];
                        $input['is_trial_period'] = $latest_receipt['is_trial_period'] == 'true' ? '1' : '0';
                        $input['is_in_intro_offer_period'] = $latest_receipt['is_in_intro_offer_period'] == 'true' ? '1' : '0';
                        $input['web_order_line_item_id'] = $latest_receipt['web_order_line_item_id'];
                        $input['subscription_group_identifier'] = $latest_receipt['subscription_group_identifier'];


                        //fetch plan
                        $plan = SubscriptionPlans::where('product_id', $latest_receipt['product_id'])->first();

                        $input['plan_id'] = $plan->id;
                        $input['platform'] = $input['platform'];
                        $input['apple_id'] = $input['apple_id'];

                        $input['user_id'] = $user->id;


                        $subscription = $subscription->create($input); //create subscription record

                        if ($subscription) {
                            $featuresData = [
                                'subscription_id' => $subscription->id,
                                'available_boost' => $plan->boost_count,
                                'boost_reset_on' => Carbon::now()->addMonth(),
                                'available_likes' => $plan->unlimited_likes ? '99999999' : '100', //unlimited or 100
                                'available_super_likes' => $plan->super_likes_count,
                                'super_likes_reset_on' => Carbon::now()->addDay(),
                                'available_last_likes' => $plan->last_likes,
                                'available_top_picked' => $plan->top_picks_count,
                                'visible_top_picks' => $plan->top_picks_visible,
                                'top_picked_reset_on' => Carbon::now()->addDay(),
                                'rewind' => $plan->unlimited_rewinds,
                            ];

                            // dd($featuresData);
                            // $previous_subscription_id = $user->subscriptions->id;

                            $features = SubscriptionFeaturesStatus::create($featuresData); //create subscription features status record
                            if ($features) {
                                //deactivate previous subscription if any
                                if ($user->subscriptions) {
                                    $deactivatePlan = Subscriptions::where('id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                                    $deactivateFeatures = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                                    $deactivateReceipt = SubscriptionFeaturesStatus::where('subscription_id', $user->subscription_receipt->id)->where('is_active', 1)->update(['is_active' => 0]);
                                }

                                //store receipt data
                                // $receipt = $receipts->create([
                                //                             'receipt_data' => $latest_receipt['latest_receipt'],
                                //                             'user_id' => $user->id,
                                //                             'subscription_id' => $subscription->id
                                //                         ]);

                                return response()->json(array('success' => 1, 'subscription_info' => $subscription->load('user_features_status', 'subscription_plan'), 'message' => __('subscriptions.subscribed_to_plan')), 200, [], JSON_NUMERIC_CHECK);
                            } else {
                                return response()->json(array('success' => 0, 'message' => __('errors.something_went_wrong')), 200, [], JSON_NUMERIC_CHECK);
                            }
                        } else {
                            return response()->json(array('success' => 0, 'message' => __('errors.something_went_wrong')), 200, [], JSON_NUMERIC_CHECK);
                        }
                    }
                }
            } catch (\Exception $e) {
                $message = array('success' => 0, 'message' => $e->getMessage());
                return response()->json($message);
            }
        } else {
            $message = array('success' => 0, 'message' => __('errors.user_not_found'));
            return response()->json($message);
        }
    }



    public function androidAddSubscription(Request $request, Subscriptions $subscription)
    {
        $user = User::with('subscriptions', 'subscription_receipt')->whereId(auth()->user()->id)->first();

        $input = $request->all();

        $validator = Validator::make($input, [
            'plan_id' => 'required|exists:subscription_plans,id',
            'platform' => 'required',
            'subscription_id' => 'required',
            'payment_token' => 'required'
        ]);
        if ($validator->fails()) {
            $ret = array('success' => 0, 'message' => $validator->messages()->first());
            return response()->json($ret);
        }

        if ($user) {
            $package_name = env('GOOGLE_PACKAGE_NAME', 'com.app.tundur');

            $access_token = Settings::where('key', 'ANDROID_ACCESS_TOKEN')->pluck('value')->first();
            $refresh_token = Settings::where('key', 'ANDROID_REFRESH_TOKEN')->pluck('value')->first();
            $expires_in = Settings::where('key', 'EXPIIRES_IN')->pluck('value')->first();

            $currentTime = strtotime(date('Y-m-d H:i:s'));
            if ($expires_in) {
                if ($expires_in < $currentTime) {
                    $refreshToken = $this->refreshAccessToken($access_token, $refresh_token, $expires_in);
                } else {
                    $refreshToken['access_token'] = $access_token;
                }
            } else {
                $refreshToken = $this->refreshAccessToken($access_token, $refresh_token, $expires_in);
            }

            try {
                // dd($input['subscription_id']);
                $android_subscription_id = $input['subscription_id'];
                if (strpos($input['subscription_id'], '_dev') != false) {
                    $android_subscription_id = str_replace('_dev', '_de', $input['subscription_id']);
                }
                $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/" . $package_name . "/purchases/subscriptions/" . $android_subscription_id . "/tokens/" . $input['payment_token'];
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $refreshToken['access_token']
                ])->get($url);

                $response = json_decode($response->body(), true);

                // dd($response);
                if(isset($response['error']) && isset($response['error']['message'])) {
                    $ret = ['success' => 0, 'message' => $response['error']['message']];
                    return response()->json($ret);
                }

                // $latest_receipt = (array) $response->latest_receipt_info[count($response->latest_receipt_info) - 1];

                // //check if already any subscription with same transaction id exists
                $check = $subscription;

                if ($check->where([
                    'user_id' => $user->id,
                    'transaction_id' => $response['orderId'],
                    'purchase_token' => $input['payment_token']
                ])
                    ->exists()
                ) {
                    return response()->json(array('success' => 0, 'message' => __('subscriptions.subscription_already_exists')), 200, [], JSON_NUMERIC_CHECK);
                }

                $subsc['purchase_date'] = Carbon::createFromTimestampMs($input['purchaseTime'])->format('Y-m-d H:i:s');
                $subsc['expires_date'] = Carbon::createFromTimestampMs($response['expiryTimeMillis'])->format('Y-m-d H:i:s');
                $subsc['transaction_id'] = $response['orderId'];
                $subsc['product_id'] = $input['subscription_id'];
                $subsc['quantity'] = $response['quantity'] ?? 1;
                // $input['is_trial_period'] = $latest_receipt['is_trial_period'] == 'true' ? '1' : '0';
                // $input['is_in_intro_offer_period'] = $latest_receipt['is_in_intro_offer_period'] == 'true' ? '1' : '0';
                // $input['web_order_line_item_id'] = $latest_receipt['web_order_line_item_id'];
                // $input['subscription_group_identifier'] = $latest_receipt['subscription_group_identifier'];


                $subsc['plan_id'] = $input['plan_id'];
                $subsc['platform'] = $input['platform'];
                $subsc['android_id'] = $input['android_id'];

                $subsc['user_id'] = $user->id;
                $subsc['purchase_token'] = $input['payment_token'];
                // dd($subsc);
                $subscription = $subscription->create($subsc); //create subscription record

                if ($subscription) {
                    $plan = SubscriptionPlans::where('id', $input['plan_id'])->first();

                    $featuresData = [
                        'subscription_id' => $subscription->id,
                        'available_boost' => $plan->boost_count,
                        'boost_reset_on' => Carbon::now()->addMonth(),
                        'available_likes' => $plan->unlimited_likes ? '99999999' : '100', // unlimited or 100
                        'available_super_likes' => $plan->super_likes_count,
                        'super_likes_reset_on' => Carbon::now()->addDay(),
                        'available_last_likes' => $plan->last_likes,
                        'available_top_picked' => $plan->top_picks_count,
                        'visible_top_picks' => $plan->top_picks_visible,
                        'top_picked_reset_on' => Carbon::now()->addDay(),
                        'rewind' => $plan->unlimited_rewinds,
                    ];
                    // dd($user->toArray());
                    // $previous_subscription_id = $user->subscriptions->id;

                    $features = SubscriptionFeaturesStatus::create($featuresData); //create subscription features status record
                    if ($features) {
                        //deactivate previous subscription if any
                        if ($user->subscriptions) {
                            $deactivatePlan = Subscriptions::where('id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateFeatures = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                        }

                        return response()->json(array('success' => 1, 'data' => $subscription->load('user_features_status', 'subscription_plan'), 'message' => __('subscriptions.subscribed_to_plan')), 200, [], JSON_NUMERIC_CHECK);
                    } else {
                        return response()->json(array('success' => 0, 'message' => __('errors.something_went_wrong')), 200, [], JSON_NUMERIC_CHECK);
                    }
                } else {
                    return response()->json(array('success' => 0, 'message' => __('errors.something_went_wrong')), 200, [], JSON_NUMERIC_CHECK);
                }
            } catch (\Exception $e) {
                $message = array('success' => 0, 'message' => $e->getMessage());
                return response()->json($message);
            }
        } else {
            $message = array('success' => 0, 'message' => __('errors.user_not_found'));
            return response()->json($message);
        }
    }

    public function refreshAccessToken($access_token, $refresh_token, $expires_in)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json'
        ])->post('https://accounts.google.com/o/oauth2/token', [
            'grant_type' => 'refresh_token',
            'client_id' => env('GOOGLE_CLIENT_ID', '504001580954-qqn03pqn32dko3s4fgjg5od0ak89239a.apps.googleusercontent.com'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET_KEY', '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'),
            'refresh_token' => $refresh_token
        ]);

        $response = json_decode($response->body(), true);

        $access_token = Settings::where('key', 'ANDROID_ACCESS_TOKEN')->pluck('value');
        $refresh_token = Settings::where('key', 'ANDROID_REFRESH_TOKEN')->pluck('value');
        $currentTime = strtotime(date('Y-m-d H:i:s'));
        $expiry_time = $currentTime + $response['expires_in'] - 10;
        if ($access_token) {
            Settings::where('key', 'ANDROID_ACCESS_TOKEN')->update(['value' => $response['access_token']]);
            Settings::where('key', 'EXPIIRES_IN')->update(['value' => $expiry_time]);
        } else {
            $rows = [
                [
                    'key' => 'ANDROID_ACCESS_TOKEN',
                    'value' => $response['access_token']
                ],
                [
                    'key' => 'ANDROID_REFRESH_TOKEN',
                    'value' => $refresh_token
                ],
                [
                    'key' => 'EXPIIRES_IN',
                    'value' => $expiry_time
                ],
            ];
            foreach ($rows as $data) {
                Settings::Create($data);
            }
        }
        return $response;
    }


    /**
     * Check current subscription status for android
     *
     * @return \Illuminate\Http\Response
     */
    public function androidCheckCurrentSubscriptionStatus(Request $request, Subscriptions $subscription)
    {
        $user = User::with('subscriptions', 'subscription_plan')->whereId(auth()->user()->id)->first();

        $input = $request->all();

        $validator = Validator::make($input, [
            'platform' => 'required',
            'android_id' => 'required',
        ]);
        if ($validator->fails()) {
            $ret = array('success' => 0, 'message' => $validator->messages()->first());
            return response()->json($ret);
        }

        // dd($user->subscriptions->toArray());

        if ($user) {
            $package_name = env('GOOGLE_PACKAGE_NAME', 'com.app.tundur');
            try {
                // print_r($user->subscriptions->toArray());
                // die;
                //if default free plan
                // dd($user->subscriptions);
                if ($user->subscriptions == null) {
                    // assign default subscription
                    //trigger AssignDefaultPlan event
                    $eventData = ['platform' => $input['platform'], 'plan_id' => 1, 'transaction_id' => 0, 'product_id' => '0'];
                    event(new AssignDefaultPlan($user, $eventData));


                    /****Get available plan and available features of user****/
                    $activeSubscription = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                        ->where('subscriptions.user_id', $user->id)
                        ->where('is_active', 1)
                        ->first();
                    /*********************************************************/

                    return response()->json(array('success' => 1, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_valid')), 200, [], JSON_NUMERIC_CHECK);
                }

                if ($user->subscriptions->product_id == false) {
                    /****Get available plan and available features of user****/
                    $activeSubscription = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                        ->where('subscriptions.user_id', $user->id)
                        ->where('is_active', 1)
                        ->first();
                    /*********************************************************/

                    return response()->json(array('success' => 1, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_valid')), 200, [], JSON_NUMERIC_CHECK);
                }
                // hit google API for auth token refresh
                $access_token = Settings::where('key', 'ANDROID_ACCESS_TOKEN')->pluck('value')->first();
                $refresh_token = Settings::where('key', 'ANDROID_REFRESH_TOKEN')->pluck('value')->first();
                $expires_in = Settings::where('key', 'EXPIIRES_IN')->pluck('value')->first();

                $currentTime = strtotime(date('Y-m-d H:i:s'));
                if ($expires_in) {
                    if ($expires_in < $currentTime) {
                        $refreshToken = $this->refreshAccessToken($access_token, $refresh_token, $expires_in);
                    } else {
                        $refreshToken['access_token'] = $access_token;
                    }
                } else {
                    $refreshToken = $this->refreshAccessToken($access_token, $refresh_token, $expires_in);
                }

                $android_subscription_id = $user->subscriptions->product_id;
                if (strpos($user->subscriptions->product_id, '_dev') != false) {
                    $android_subscription_id = str_replace('_dev', '_de', $user->subscriptions->product_id);
                }
                $url = "https://androidpublisher.googleapis.com/androidpublisher/v3/applications/" . $package_name . "/purchases/subscriptions/" . $android_subscription_id . "/tokens/" . $user->subscriptions->purchase_token;
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $refreshToken['access_token']
                ])->get($url);

                $response = json_decode($response->body(), true);
                // dd($response);
                // $latest_receipt = (array) $response->latest_receipt_info[count($response->latest_receipt_info) - 1];

                // //check if already any subscription with same transaction id exists
                // $check = $subscription;

                // if ($check->where([
                //     'user_id' => $user->id,
                //     'transaction_id' => $response['orderId'],
                //     'purchase_token' => $user->subscriptions->purchase_token
                // ])
                //     ->exists()
                // ) {
                //     return response()->json(array('success' => 0, 'message' => __('subscriptions.subscription_already_exists')), 200, [], JSON_NUMERIC_CHECK);
                // }
                // dd($latest_receipt);

                //check if latest receipt has same subscription info
                $sameReceiptAndTransaction = false;
                if (($user->subscriptions->transaction_id == $response['orderId'])) {
                    $sameReceiptAndTransaction = true;
                    //
                }
                // dd($sameReceiptAndTransaction);
                //check if subscription expired
                if ($sameReceiptAndTransaction) {
                    // $expired = false;
                    if (($user->subscriptions->expires_date)->lessThan(Carbon::now())) {
                        //deactivate previous subscription if any
                        if ($user->subscriptions) {
                            $deactivatePlan = Subscriptions::where('id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateFeatures = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                        }

                        //trigger AssignDefaultPlan event
                        $eventData = ['platform' => $input['platform'], 'plan_id' => 1, 'transaction_id' => 0, 'product_id' => '0'];
                        event(new AssignDefaultPlan($user, $eventData));


                        /****Get available plan and available features of user****/
                        $activeSubscription  = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                            ->where('subscriptions.user_id', $user->id)
                            ->where('is_active', 1)
                            ->first();
                        /*********************************************************/


                        return response()->json(array('success' => 0, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_expired')), 200, [], JSON_NUMERIC_CHECK);
                    } else {

                        /****Get available plan and available features of user****/
                        $activeSubscription  = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                            ->where('subscriptions.user_id', $user->id)
                            ->where('is_active', 1)
                            ->first();
                        /*********************************************************/

                        return response()->json(array('success' => 1, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_valid')), 200, [], JSON_NUMERIC_CHECK);
                    }
                }
                //if latest_receipt has new subscription info
                else {
                    //check if this subscription is expired
                    if (Carbon::createFromTimestampMs($response['expiryTimeMillis'])->lessThan(Carbon::now())) {
                        //deactivate previous subscription if any
                        if ($user->subscriptions) {
                            $deactivatePlan = Subscriptions::where('id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateFeatures = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                            $deactivateReceipt = SubscriptionFeaturesStatus::where('subscription_id', $user->subscription_receipt->id)->where('is_active', 1)->update(['is_active' => 0]);
                        }

                        //trigger AssignDefaultPlan event
                        $eventData = ['platform' => $input['platform'], 'plan_id' => 1, 'transaction_id' => 0, 'product_id' => '0'];
                        event(new AssignDefaultPlan($user, $eventData));


                        /****Get available plan and available features of user****/
                        $activeSubscription  = Subscriptions::with('user_features_status', 'subscription_plan', 'consumables')
                            ->where('subscriptions.user_id', $user->id)
                            ->where('is_active', 1)
                            ->first();
                        /*********************************************************/

                        return response()->json(array('success' => 0, 'subscription_info' => $activeSubscription, 'message' => __('subscriptions.subscription_expired')), 200, [], JSON_NUMERIC_CHECK);
                    }
                    //if not than add new subscription record and deactivate previous
                    else {

                        $subsc['purchase_date'] = Carbon::createFromTimestampMs($input['purchaseTime'])->format('Y-m-d H:i:s');
                        $subsc['expires_date'] = Carbon::createFromTimestampMs($response['expiryTimeMillis'])->format('Y-m-d H:i:s');
                        $subsc['transaction_id'] = $response['orderId'];
                        $subsc['product_id'] = $response['subscription_id'];
                        $subsc['quantity'] = $response['quantity'] ?? 1;
                        // $input['is_trial_period'] = $latest_receipt['is_trial_period'] == 'true' ? '1' : '0';
                        // $input['is_in_intro_offer_period'] = $latest_receipt['is_in_intro_offer_period'] == 'true' ? '1' : '0';
                        // $input['web_order_line_item_id'] = $latest_receipt['web_order_line_item_id'];
                        // $input['subscription_group_identifier'] = $latest_receipt['subscription_group_identifier'];

                        $plan = SubscriptionPlans::where('product_id', $response['subscription_id'])->first();
                        $subsc['plan_id'] = $plan->id;
                        $subsc['platform'] = $input['platform'];
                        $subsc['android_id'] = $input['android_id'];

                        $subsc['user_id'] = $user->id;
                        $subsc['purchase_token'] = $input['payment_token'];
                        // dd($subsc);
                        $subscription = $subscription->create($subsc); //create subscription record

                        if ($subscription) {
                            $featuresData = [
                                'subscription_id' => $subscription->id,
                                'available_boost' => $plan->boost_count,
                                'boost_reset_on' => Carbon::now()->addMonth(),
                                'available_likes' => $plan->unlimited_likes ? '99999999' : '100', //unlimited or 100
                                'available_super_likes' => $plan->super_likes_count,
                                'super_likes_reset_on' => Carbon::now()->addDay(),
                                'available_last_likes' => $plan->last_likes,
                                'available_top_picked' => $plan->top_picks_count,
                                'visible_top_picks' => $plan->top_picks_visible,
                                'top_picked_reset_on' => Carbon::now()->addDay(),
                                'rewind' => $plan->unlimited_rewinds,
                            ];

                            // dd($featuresData);
                            // $previous_subscription_id = $user->subscriptions->id;

                            $features = SubscriptionFeaturesStatus::create($featuresData); //create subscription features status record
                            if ($features) {
                                //deactivate previous subscription if any
                                if ($user->subscriptions) {
                                    $deactivatePlan = Subscriptions::where('id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                                    $deactivateFeatures = SubscriptionFeaturesStatus::where('subscription_id', $user->subscriptions->id)->where('is_active', 1)->update(['is_active' => 0]);
                                    $deactivateReceipt = SubscriptionFeaturesStatus::where('subscription_id', $user->subscription_receipt->id)->where('is_active', 1)->update(['is_active' => 0]);
                                }

                                //store receipt data
                                // $receipt = $receipts->create([
                                //                             'receipt_data' => $latest_receipt['latest_receipt'],
                                //                             'user_id' => $user->id,
                                //                             'subscription_id' => $subscription->id
                                //                         ]);

                                return response()->json(array('success' => 1, 'subscription_info' => $subscription->load('user_features_status', 'subscription_plan'), 'message' => __('subscriptions.subscribed_to_plan')), 200, [], JSON_NUMERIC_CHECK);
                            } else {
                                return response()->json(array('success' => 0, 'message' => __('errors.something_went_wrong')), 200, [], JSON_NUMERIC_CHECK);
                            }
                        } else {
                            return response()->json(array('success' => 0, 'message' => __('errors.something_went_wrong')), 200, [], JSON_NUMERIC_CHECK);
                        }
                    }
                }
            } catch (\Exception $e) {
                $message = array('success' => 0, 'message' => $e->getMessage());
                return response()->json($message);
            }
        } else {
            $message = array('success' => 0, 'message' => __('errors.user_not_found'));
            return response()->json($message);
        }
    }
}
