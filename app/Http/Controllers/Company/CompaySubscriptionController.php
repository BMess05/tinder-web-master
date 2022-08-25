<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Company;
use App\Model\CompanySubscription;
use App\Model\CompanySubscriptionPlan;
use Stripe;
use Session;
use Exception;

class CompaySubscriptionController extends Controller
{
    protected $stripe;

    public function __construct() 
    {
        $this->stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
    }

    public function index()
    {
        if(!is_null(auth('companies')->user()->stripe_id)) {
            if(auth('companies')->user()->subscription == 1) {
                return redirect()->route('companyDashboard')->with(['status' => 'success', 'message' => 'Your subscription is active already.']);
            }
        }

        $plans = CompanySubscriptionPlan::select('id', 'name', 'slug', 'interval', 'cost')->where('active', 1)->get();
        return view('companies.subscription.index', compact('plans'));
    }

    public function show($slug, Request $request) {
        $paymentMethods = $request->user()->paymentMethods();

        $intent = $request->user()->createSetupIntent();
        $plan = CompanySubscriptionPlan::where('slug', $slug)->first();
        return view('companies.subscription.create', compact('plan', 'intent'));
    }

    public function create(Request $request)
    {
        $plan = CompanySubscriptionPlan::where('slug', $request->get('plan'))->first();
        $data = $request->all();
        $user = $request->user(); // auth()->user();
        $input = $request->all();
        $token =  $request->stripeToken;
        $paymentMethod = $request->paymentMethod;
        try {
            Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            
            if (is_null($user->stripe_id)) {
                $stripeCustomer = $user->createAsStripeCustomer();
            }
            Stripe\Customer::createSource(
                $user->stripe_id,
                ['source' => $token]
            );
            
            $date = new \DateTime();
            $today = $date->format('Y-m-d H:i:s');
            $ends_obj = $date->modify('+ 1 '.$plan->interval);
            $ends_at = $ends_obj->format('Y-m-d H:i:s');
            if($ends_at <= $today) {
                // echo "Subscription is expired."; die;
                return back()->with('danger', 'Subscription is expired.');
            }
            $subscription = new CompanySubscription();
            $subscription->user_id = $user->id;
            $subscription->name = $plan->name;
            $subscription->stripe_id = $user->stripe_id;
            $subscription->stripe_status = 'active';
            $subscription->stripe_plan = $plan->stripe_plan;
            $subscription->quantity = 1;
            $subscription->starts_at = $today;
            $subscription->ends_at = $ends_at;
            if($subscription->save()) {
                Company::where('id', $user->id)->update(['subscription' => 1]);
                return redirect()->route('companyDashboard')->with(['status' => 'success', 'message' => 'Subscription is completed.']);
                // return back()->with('success','Subscription is completed.');
            }   else {
                // echo "Something went wrong, please try again."; die;
                return back()->with('danger','Something went wrong, please try again.');
            }
        } catch (Exception $e) {
            echo "EXception: " . $e->getMessage(); die;
            return back()->with('danger', $e->getMessage());
        }
    }

    public function subscription_expired(Request $request) {
        return Company::where('id', 6)->update(['subscription' => 0]);
    }
}
