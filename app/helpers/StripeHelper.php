<?php

namespace App\Helpers;

use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Transfer;

class StripeHelper
{

    /**
     *  create a stripe customer for a user
     */
    public static function createPlan($donationAmount)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            $name = $donationAmount->badge->name ?? $donationAmount->amount."_plan";
            $plan = $stripe->plans->create([
                'amount' => $donationAmount->amount,
                'currency' => config('common.stripe_currency'),
                'interval' => 'month',
                'product' => ['name' => $name],
            ]);
            $donationAmount->update(['stripe_plan_id' => $plan->id]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return $customer;
    }

    /**
     *  create a stripe customer for a user
     */
    public static function addCustomer($user)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));

            $customer = $stripe->customers->create([
                'email' => $user->email,
                'name' => $user->name,
                'phone' => $user->phone_number,
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return $customer;
    }

    public static function updateCustomer($user)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));

            $customer = $stripe->customers->update($user->stripe_customer_id, [
                'email' => $user->email,
                'name' => $user->name,
                'phone' => $user->phone_number,
            ]);

            $user->update(['stripe_customer_id' => $customer->id]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return $customer;
    }

    public static function getCustomer($user)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            $customer = $stripe->customers->retrieve($user->stripe_customer_id);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return $customer;
    }

    public static function createCard($user, $data)
    {
        try {
            if (!$user->stripe_customer_id) {
                self::addCustomer($user);
            }
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            $card = $stripe->customers->createSource($user->stripe_customer_id, [
                'source' => $data
            ]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return $card;
    }

    public static function allCards($user)
    {
        try {
            if(!$user->stripe_customer_id)
            {
                self::addCustomer($user);
            }
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            $cards = $stripe->customers->allSources($user->stripe_customer_id, ['object' => 'card', 'limit' => 3]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return $cards->data;
    }

    public static function deleteCard($user, $cardId)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            $cards = $stripe->customers->deleteSource($user->stripe_customer_id, $cardId, []);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return 1;
    }

    public static function createDirectCharge($user, $cardToken, $amount, $desc=null)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));

            $charge = $stripe->charges->create([
                'amount'        => $amount,
                'currency'      => config('common.stripe_currency'),
                'source'        => $cardToken,
                'description'   => $desc ?? 'Charges for Donation',
            ]);

        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }

        return $charge;
    }

    public static function createCharge($user, $cardId, $amount, $desc=null)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            $customer = $stripe->customers->retrieve($user->stripe_customer_id);

            $charge = $stripe->charges->create([
                'amount'        => $amount * 100,
                'currency'      => config('common.stripe_currency'),
                'customer'      => $customer,
                'source'        => $cardId,
                'description'   => $desc ?? 'Charges for land',
            ]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }

        return $charge;
    }

    public static function createRefund($user, $booking, $reason)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            $refund = $stripe->refunds->create([
                'charge' => $booking->charge_id,
                // 'reason' => $reason,
            ]);
            $booking->update(['refund_id' => $refund->id]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return $refund;
    }

    public static function createPrice($priceAmount)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));

            $price = $stripe->prices->create([
                'unit_amount' => $priceAmount->amount * 100,
                'currency' => config('common.stripe_currency'),
                'recurring' => ['interval' => 'month'],
                'product_data' => ['name' => $priceAmount->name],
            ]);

            $priceAmount->update(['stripe_price_id' => $price->id]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }

        return $price;
    }

    public static function cancelSubscription($user)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            if ($user->stripe_subscription_id) {
                $cancel = $stripe->subscriptions->cancel($user->stripe_subscription_id, []);
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        return $cancel;
    }

    public static function createSubscription($user, $stripePrice)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));
            $priceId = $stripePrice->stripe_price_id;
            $subscription = null;

            if (!$priceId) {
                $price = self::createPrice($stripePrice);
                if ($price) {
                    $priceId = $price->id;
                }
            }

            if (!$user->stripe_customer_id) {
                $customer = self::addCustomer($user);
            }

            if ($priceId) {
                if ($user->stripe_subscription_id) {
                    $oldSubscription = $stripe->subscriptions->retrieve($user->stripe_subscription_id);

                    $subscription = $stripe->subscriptions->update($user->stripe_subscription_id, [
                        'cancel_at_period_end' => false,
                        'proration_behavior' => 'create_prorations',
                        'items' => [
                            [
                                'id' => $oldSubscription->items->data[0]->id,
                                'price' => $priceId,
                            ],
                        ],
                    ]);
                } else {
                    $subscription = $stripe->subscriptions->create([
                        'customer' => $user->stripe_customer_id,
                        'items' => [
                            ['price' => $priceId],
                        ],
                    ]);
                    $user->update(['stripe_subscription_id' => $subscription->id]);
                }
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }

        return $subscription;
    }

    public static function connectPayment($user, $booking, $cardId)
    {
        try {
            Stripe::setApiKey(config('common.stripe_secret_key'));

            $charge = Charge::create([
                'amount'      => ($booking->stripe_total_amount) * 100,
                'currency'    => 'usd',
                'customer'    => $user->stripe_customer_id,
                'source'      => $cardId,
                'description' => 'Booking id '.$booking->id
            ]);

            // if($booking->payment_method == 1 && $booking->stripe_total_amount > 0)
            // {
            //     $transfer = Transfer::create([
            //         'amount'             => ($booking->amount - $booking->discount) * 100,
            //         'currency'           => 'usd',
            //         'destination'        => $booking->host->stripe_account_id ?? 'acct_1KJchtQRgteje3fw',
            //         // 'destination'        => 'acct_1KJchtQRgteje3fw',
            //     ]);
            // }
            // else
            // {
            //     $transfer = Transfer::create([
            //         'amount'             => ($booking->amount - $booking->discount) * 100,
            //         'currency'           => 'usd',
            //         'source_transaction' => $charge->id,
            //         'destination'        => $booking->host->stripe_account_id ?? 'acct_1KJchtQRgteje3fw',
            //         // 'destination'        => 'acct_1KJchtQRgteje3fw',
            //     ]);
            // }

        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return $e->getMessage();
        }

        return $charge;
    }

    public static function transferPayment($user, $booking, $cardId)
    {
        try {
            Stripe::setApiKey(config('common.stripe_secret_key'));

            if($booking->payment_method == 1 && $booking->stripe_total_amount > 0)
            {
                $transfer = Transfer::create([
                    'amount'             => ($booking->amount - $booking->discount - $booking->tax) * 100,
                    'currency'           => 'usd',
                    'destination'        => $booking->host->stripe_account_id ?? 'acct_1KJchtQRgteje3fw',
                    // 'destination'        => 'acct_1KJchtQRgteje3fw',
                ]);
            }
            else
            {
                $transfer = Transfer::create([
                    'amount'             => ($booking->amount - $booking->discount - $booking->tax) * 100,
                    'currency'           => 'usd',
                    // 'source_transaction' => $charge->id,
                    'destination'        => $booking->host->stripe_account_id ?? 'acct_1KJchtQRgteje3fw',
                    // 'destination'        => 'acct_1KJchtQRgteje3fw',
                ]);
            }

        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return $e->getMessage();
        }

        return $transfer;
    }


    public static function createAccount($user)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('common.stripe_secret_key'));

            $account = $stripe->accounts->create([
                'type' => 'custom',
                'country' => 'us',
                'email' => $user->email ?? '',
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'individual',
                'individual' => [
                    'address' => [
                        'line1' => $user->address ?? '',
                        'city' => $user->city ?? '',
                        'country' => 'us',
                        'state' => $user->state ?? '',
                        'postal_code' => $user->post_code ?? '',
                    ],
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                    'email' => $user->email ?? '',
                    'gender' => 'male',
                    'phone' => $user->phone_number ?? '',
                    'dob' => ['day' => rand(0, 31), 'month' => rand(1, 12), 'year' => 1990],
                ],
                'business_profile' => [
                    'mcc' => '5734',
                    'name' => $user->first_name.' '.$user->last_name ?? '',
                    'support_phone' => $user->phone_number ?? '',
                    'support_email' => $user->email ?? '',
                    'support_address' => [
                        'line1' => $user->address ?? '',
                        'city' => $user->city ?? '',
                        'country' => 'us',
                        'state' => $user->state ?? '',
                        'postal_code' => $user->post_code ?? '',
                    ],
                    'support_url' => 'https://landburro.com',
                    'url' => 'https://landburro.com',
                ],
                'tos_acceptance' => [
                    'date' => time(),
                    'ip' => request()->ip(),
                ],
            ]);

            $user->update(['stripe_account_id' => $account->id]);
        } catch (\Exception $e) {
            return $e;
        }
        return $account;
    }
}
