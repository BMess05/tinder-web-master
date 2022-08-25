<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\CompanySubscriptionPlan;
use App\Model\Company;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreCompanyPlanRequest;

class CompanyPlanController extends Controller
{
    protected $stripe;

    public function __construct() {
        $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    }

    public function listPlans() {
        $plans = CompanySubscriptionPlan::orderBy('id', 'DESC')->get();
        return view('companies.plans.list', compact('plans'));
    }

    public function createPlan() {
        return view('companies.plans.create');
    }

    public function storePlan(StoreCompanyPlanRequest $request) {   
        $data = $request->except('_token');
        // dd($data);
        DB::beginTransaction();
        $data['slug'] = str_replace(' ', '-', strtolower($data['name']));
        $price = $data['cost'] *100; 

        try {
            //create stripe product
            $stripeProduct = $this->stripe->products->create([
                'name' => $data['name'],
            ]);
            
            //Stripe Plan Creation
            $stripePlanCreation = $this->stripe->plans->create([
                'amount' => $price,
                'currency' => 'inr',
                'interval' => $data['interval'] ?? 'month', // it can be day, week, month or year
                'product' => $stripeProduct->id,
            ]);

            $data['stripe_plan'] = $stripePlanCreation->id;
            $data['stripe_product'] = $stripeProduct->id;

            CompanySubscriptionPlan::create($data);
            DB::commit();
            return redirect()->route('listPlans')->with(['status' => 'success', 'message' => 'Subscription plan created successfully.']);
        }   catch(\Exception $e) {
            DB::rollback();
            return redirect()->back()->with(['status' => 'danger', 'message' => $e->getMessage()])->withInput();
        }
    }

    public function deletePlan($id) {
        $plan = CompanySubscriptionPlan::find($id);
        if(!$plan) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid Plan Id.'])->withInput();
        }
        $plan_id = $plan->stripe_plan;
        $product_id = $plan->stripe_product;
        try {
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $res1 = $stripe->plans->delete(
                $plan_id,
                []
            );
            

            $res = $stripe->products->delete(
                $product_id,
                []
            );
        } catch(\Exception $e) {
            // return redirect()->back()->with(['status' => 'danger', 'message' => $e->getMessage()])->withInput();
        }
        
        if($plan->delete()) {
            return redirect()->back()->with(['status' => 'success', 'message' => 'Plan deleted successfully.']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    public function activeInactivePlan(Request $request) {
        $data = $request->all();
        if(\Auth::user()->role_id == 0) {
            $plan = CompanySubscriptionPlan::find($data['id']);
            if(!$plan) {
                return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid Plan Id.'])->withInput();
            }
            $plan_id = $plan->stripe_plan;
            $product_id = $plan->stripe_product;
            try {
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));

                if($plan->active == 0) {
                    $stripe->products->update($product_id, ['active' => true]);
                    $plan->active = 1;
                    $msg = "Plan set to active.";
                }   else {
                    $stripe->products->update($product_id, ['active' => false]);
                    $plan->active = 0;
                    $msg = "Plan set to inactive.";
                }
                if($plan->save()) {
                    return response()->json([
                        "success" => 1,
                        "message" => $msg
                    ]);
                }   else {
                    return response()->json([
                        "success" => 0,
                        "message" => 'Something went wrong, try again.'
                    ]);
                }
            }   catch(\Exception $e) {
                return response()->json([
                    "success" => 0,
                    "message" => $e->getMessage()
                ]);
            }
            
        }   else {
            return response()->json([
                "success" => 0,
                "message" => 'Not authorised for this action.'
            ]);
        }

    }
    
    /**
     * Show the Plan.
     *
     * @return mixed
     */
    public function show(Plan $plan, Request $request) {   
        $paymentMethods = $request->user()->paymentMethods();

        $intent = $request->user()->createSetupIntent();
        
        return view('plans.show', compact('plan', 'intent'));
    }

}
