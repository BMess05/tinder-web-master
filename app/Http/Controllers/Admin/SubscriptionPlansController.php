<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\SubscriptionPlans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SubscriptionPlansController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index( SubscriptionPlans $plans)
    {
        $plans = $plans->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.subscription_plans.list', compact(['plans']));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.subscription_plans.add', compact([]));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Model\SubscriptionPlans  $subscriptionPlans
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request , SubscriptionPlans $subscriptionPlans)
    {
        $data = $request->all();

        $validator = Validator::make($data,[
            'plan_name'    => ['required',Rule::unique('subscription_plans')->where(function ($query) {
                                                return $query->where('deleted_at', '=', null);
                                            }), 'string'],
            "last_likes" => "required | boolean",
            "last_likes_duration" => "required",
            "super_likes" => "required | boolean",
            "super_likes_count" => "required",
            "super_likes_duration" => "required",
            "unlimited_likes" => "required | boolean",
            "passport" => "required | boolean",
            "unlimited_rewinds" => "required | boolean",
            "ads" => "required | boolean",
            "top_picks" => "required | boolean",
            "top_picks_visible" => "required",
            "see_who_likes_me" => "required | boolean",
            "priority_likes" => "required | boolean",
            "attach_message" => "required | boolean"
        ]);
        if($validator->fails()) {
            return redirect()->back()->with(['status' => 'danger', 'message' => $validator->messages()->first()])->withInput();
        }


        $plan = $subscriptionPlans->create($data);

        if($plan) {
            return redirect('subscription/plan/list')->with(['status' => 'success', 'message' => 'Subscription plan added.']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\SubscriptionPlans  $subscriptionPlans
     * @return \Illuminate\Http\Response
     */
    public function show(SubscriptionPlans $subscriptionPlans , $id)
    {
        $plan = $subscriptionPlans->where('id',$id)->first();
        // dd($plan);

        return view('admin.subscription_plans.show', compact(['plan']));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\SubscriptionPlans  $subscriptionPlans
     * @return \Illuminate\Http\Response
     */
    public function edit(SubscriptionPlans $subscriptionPlans , $id)
    {
        $plan = $subscriptionPlans->where('id',$id)->first();
        return view('admin.subscription_plans.edit', compact(['plan']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\SubscriptionPlans  $subscriptionPlans
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, SubscriptionPlans $subscriptionPlans )
    {
        $data = $request->all();

        $validator = Validator::make($data,[
            'plan_name'    => ['required',Rule::unique('subscription_plans')->where(function ($query) use($id) {
                                                return $query->where('deleted_at', '=', null)->where('id','!=',$id);
                                            }), 'string'],
            "last_likes" => "required | boolean",
            "last_likes_duration" => "required",
            "super_likes" => "required | boolean",
            "super_likes_count" => "required",
            "super_likes_duration" => "required",
            "unlimited_likes" => "required | boolean",
            "passport" => "required | boolean",
            "unlimited_rewinds" => "required | boolean",
            "ads" => "required | boolean",
            "top_picks" => "required | boolean",
            "top_picks_visible" => "required",
            "see_who_likes_me" => "required | boolean",
            "priority_likes" => "required | boolean",
            "attach_message" => "required | boolean"
        ]);
        if($validator->fails()) {
            return redirect()->back()->with(['status' => 'danger', 'message' => $validator->messages()->first()])->withInput();
        }

        // dd($data);
        unset($data['_token']);

        $plan = $subscriptionPlans::where('id', $id)->update($data);
        if($plan) {
            return redirect('subscription/plan/list')->with(['status' => 'success', 'message' => 'Plan updated Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\SubscriptionPlans  $subscriptionPlans
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubscriptionPlans $subscriptionPlans, $id)
    {
        $plan = $subscriptionPlans::where('id', $id)->delete();
        if($plan) {
            return redirect()->back()->with(['status' => 'success', 'message' => 'Plan deleted Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    /**
     * Publish subscription plan
     *
     * @param  \App\Model\SubscriptionPlans  $subscriptionPlans
     * @return \Illuminate\Http\Response
     */
    public function publish(SubscriptionPlans $subscriptionPlans, $id)
    {
        $plan = $subscriptionPlans::where('id', $id)->update(['is_active'=>1]);
        if($plan) {
            return redirect()->back()->with(['status' => 'success', 'message' => 'Plan published Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    /**
     * Publish subscription plan
     *
     * @param  \App\Model\SubscriptionPlans  $subscriptionPlans
     * @return \Illuminate\Http\Response
     */
    public function unpublish(SubscriptionPlans $subscriptionPlans, $id)
    {
        $plan = $subscriptionPlans::where('id', $id)->update(['is_active'=>0]);
        if($plan) {
            return redirect()->back()->with(['status' => 'success', 'message' => 'Plan unpublished successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went Wrong.'])->withInput();
        }
    }
}
