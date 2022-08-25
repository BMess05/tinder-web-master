<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\UserImage;
use App\Model\LikeDislike;
use App\Model\Match;
use App\Model\Report;
use App\Model\Interest;
use App\Model\UserAddress;
use App\Model\Company;
use App\Model\CompanySubscriptionPlan;
use DB;
class DashboardController extends Controller
{
    function dashboard() {
        $online_users = User::where(['type' => 1,'is_blocked' => 0, 'is_online' => 1])->count();
        $male_users = User::where(['type' => 1,'is_blocked' => 0, 'gender' => 1])->count();
        $female_users = User::where(['type' => 1,'is_blocked' => 0, 'gender' => 2])->count();
        $other_users = User::where(['type' => 1,'is_blocked' => 0])
                            ->where(function($q) {
                                $q->where('gender', 3);
                                // ->orWhere('gender', null);
                            })
                            ->count();

        $cities_count = UserAddress::groupBy('city')->selectRaw('count(*) as total, city')->get()->toArray();
       
     
        $all_users = User::where(['type' => 1])->count();
        $blocked_users = User::where(['type' => 1, 'is_blocked' => 1])->count();
        $premium_users = User::where(['type' => 1, 'is_premium' => 1])->count();
        $reported_users = User::where('type', 1)
        ->whereIn('id',function($query) { 
            $query->select('reported_id')->from('reports');
        })->count();
        $all_users_age = User::select(\DB::raw('id, DATE_FORMAT(NOW(), "%Y") - DATE_FORMAT(dob, "%Y") - (DATE_FORMAT(NOW(), "00-%m-%d") < DATE_FORMAT(dob, "00-%m-%d")) AS age'))
        ->where(['type' => 1, 'is_blocked' => 0])
        ->where('dob', '!=', NULL)
        ->get()->toArray();
        if($all_users_age){
            $sum = 0;
            foreach($all_users_age as $user) {
                $sum = $sum + $user['age'];
            }
            $average_age = $sum / count($all_users_age);
        }else{
             $average_age=0.00;
        }
        $companies_count = Company::count();
        $plans_count = CompanySubscriptionPlan::count();
        return view('admin/dashboard', compact(['average_age', 'online_users', 'blocked_users', 'reported_users', 'premium_users', 'male_users', 'female_users', 'other_users', 'all_users','cities_count', 'companies_count', 'plans_count']));
    }

    function list_interests() {
        $interests = Interest::orderBy('id','DESC')->get();
        return view('admin.interests.list', compact('interests'));
    }

    function add_interest() {
        return view('admin.interests.add');
    }

    function save_interest(Request $request) {
        $validatedData = $request->validate([
            'title' => 'required',
            'titleg' => 'required',
            'titleTur' => 'required'
        ]);
        
        $data = $request->all();
        //dd($data);
        $interest = new Interest();
        $interest->title = $data['title'];
        $interest->title_de = $data['titleg'];
        $interest->title_tr = $data['titleTur'];
        $interest->language = 'en';
        //$interest->save();


        /*$interestg = new Interest();
        $interestg->title = $data['titleg'];
        $interestg->language = 'de';
        $interestg->save();

        $interestur = new Interest();
        $interestur->title = $data['titleTur'];
        $interestur->language = 'tr';
        $interestur->save();*/


        if($interest->save()) {
            return redirect()->route('list_interests')->with(['status' => 'success', 'message' => 'Interest Saved Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    function edit_interest($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Select a valid interest']);
        }
        $interest1 = Interest::find($id);
        if(!$interest1) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Select a valid interest']);
        }
        $interest = $interest1->toArray();
        return view('admin.interests.edit', compact('interest'));
    }

    function update_interest($id = null, Request $request) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Select a valid interest']);
        }
        $validatedData = $request->validate([
            'title' => 'required'
        ]);
        
        $data = $request->all();
        $interest = Interest::find($id);
        if(!$interest) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Select a valid interest']);
        }
        $interest->title = $data['title'];
        $interest->title_de = $data['titleg'];
        $interest->title_tr = $data['titleTur'];
        if($interest->save()) {
            return redirect()->route('list_interests')->with(['status' => 'success', 'message' => 'Interest Updated Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    function delete_interest($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Select a valid interest']);
        }
        $interest = Interest::find($id);
        if(!$interest) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Select a valid interest']);
        }
        $result = Interest::where('id', $id)->delete();
        if($result) {
            return redirect()->route('list_interests')->with(['status' => 'success', 'message' => 'Interest Deleted Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    function send_mail(Request $request) {
        $lang=$request->lang;
        \App::setlocale($lang);

        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required|min:30',
        ]); 

        $data = $request->all(); 
        if($data['type']==2)
        {
            //feedback form submit
            $msg= __('lang.feedback-success-msg');
        }else{
            //contact us form submit
             $msg= __('lang.contact-success-msg');
        }
        \Mail::to('support@tuenduer.com')->send(new \App\Mail\ContactUsMail($data));
        return redirect()->back()->with(['status' => 'success', 'message' => $msg]);
    }

}
