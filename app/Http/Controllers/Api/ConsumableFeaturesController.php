<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

use App\Model\User;
// use App\Model\SubscriptionPlans;
// use App\Model\Subscriptions;
// use App\Model\SubscriptionFeaturesStatus;
// use App\Model\SubscriptionReceipts;
use App\Model\ConsumableFeatures;

use App\CommonHelpers;
use Carbon\Carbon;

class ConsumableFeaturesController extends Controller
{
    /**
     * Add boost consumable
     *
     * @return \Illuminate\Http\Response
     */
    public function addBoost(Request $request , ConsumableFeatures $consumableFeatures)
    {
        $user = User::with('subscriptions','subscription_receipt')->whereId(auth()->user()->id)->first();

        $input = $request->all();

        $validator = Validator::make($input,[
            'platform' => 'required',
            'consumable_quantity' => 'required',
            'consumable_type' => 'required',
        ]);
        if($validator->fails()) {
            $ret = array('success'=>0, 'message'=> $validator->messages()->first());
            return response()->json($ret); 
        }        

        if($user){
            try
            {
                $consumableBoost = $consumableFeatures->where('user_id',$user->id)->where('is_active',1)->where('consumable_type',$input['consumable_type'])->first();

                //check if already have consumable boost
                if ( $consumableBoost ){
                    $consumableBoost->consumable_quantity = $input['consumable_quantity'] + $consumableBoost->consumable_quantity;
                    $consumableBoost->consumable_type = $input['consumable_type'];
                    $consumableBoost->save();
                }else{
                    $input['user_id'] = $user->id;
                    $consumableBoost = ConsumableFeatures::create( $input );
                }
               
                return response()->json(array('success'=>1,'data'=>$consumableBoost ,'message'=>__('subscriptions.boost_purchased')) ,200,[],JSON_NUMERIC_CHECK);                          
            }
            catch(\Exception $e)
            {
                $message = array('success'=>0,'message'=>$e->getMessage());
                return response()->json($message);
            }
        }else{
            $message = array('success'=>0,'message'=>__('errors.user_not_found'));
            return response()->json($message);
        }
    }
}
