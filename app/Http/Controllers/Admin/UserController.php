<?php

namespace App\Http\Controllers\Admin;

use App\Events\AssignDefaultPlan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\User;
use App\Model\Interest;
use App\Model\ReportReason;
use App\Model\Feedback;
use App\Model\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Model\LikeDislike;
use App\Model\Match;
use App\Model\Subscriptions;
use App\Model\SubscriptionFeaturesStatus;
use App\Model\SubscriptionReceipts;
use App\Model\ChatMessage;
use App\Model\DeviceToken;
use App\Model\ConsumableFeatures;
use App\Model\UserImage;
use App\Model\UserTopPickup;
use App\Model\UserAddress;
use DB;
use App\CommonHelpers;

class UserController extends Controller
{
    function listUsers($filter = null) {
        if($filter == "online") {
            $users = User::where(['type' => 1,'is_blocked' => 0, 'is_online' => 1])->orderBy('id', 'DESC')->get();
        }   elseif($filter == "blocked") {
            $users = User::where(['type' => 1, 'is_blocked' => 1])->orderBy('id', 'DESC')->get();
        }    elseif($filter == "premium") {
            $users = User::where(['type' => 1, 'is_premium' => 1])->orderBy('id', 'DESC')->get();
        }   elseif($filter == "males") {
            $users = User::where(['type' => 1,'is_blocked' => 0, 'gender' => 1])->orderBy('id', 'DESC')->get();   
        }   elseif($filter == "females") {
            $users = User::where(['type' => 1,'is_blocked' => 0, 'gender' => 2])->orderBy('id', 'DESC')->get();
        }   elseif($filter == "others") {
            $users = User::where(['type' => 1, 'is_blocked' => 0])
            ->where(function($q) {
                $q->where('gender', 3)
                  ->orWhere('gender', null);
            })
            ->orderBy('id', 'DESC')->get();
        }  else {
            $users = User::where('type', 1)->orderBy('id', 'DESC')->get();
        } 

        foreach($users as $user) {
            if($user->interests){
                $interest = explode(',',$user->interests);
                $title = Interest::whereIn('id', $interest)->pluck('title')->toArray();
                //print_R($title);exit;
                $user->user_interest = implode(',', $title);
            }else{
                $user->user_interest = "";
            }
        }
        if($filter == "males"){
            $filter = "Male";
        }elseif($filter == "females") {
            $filter = "Female";
        }else if($filter=="others"){
            $filter = "Other";
        } 
        return view('admin.users.list', compact(['users', 'filter']));
    }

    function list_reported_users() { 
        $users = User::where('type', 1)->withCount('reported_ids')
        ->whereIn('id',function($query) { 
            $query->select('reported_id')->from('reports');
        })
        ->orderBy('id', 'DESC')->get();

        // echo "<pre>"; print_r($users->toArray()); exit;
        return view('admin.users.reported_users_list', compact(['users']));
    }

    function blockUser($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        $user = User::find($id);
        if(!$user) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'User not found.']);
        }   else {
            $res = User::where('id', $id)->update(['is_blocked' => 1]);
            if($res) {
                return redirect()->back()->with(['status' => 'success', 'message' => 'User blocked successfully']);
            }   else {
                return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.']);
            }
        }
    }

    function unblockUser($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        $user = User::find($id);
        if(!$user) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'User not found.']);
        }   else {
            $res = User::where('id', $id)->update(['is_blocked' => 0]);
            if($res) {
                return redirect()->back()->with(['status' => 'success', 'message' => 'User unblocked successfully']);
            }   else {
                return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.']);
            }
        }
    }

    function addUser() {
        return view('admin.users.add');
    }

    function saveUser(Request $request) { 
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,NULL,id,deleted_at,NULL',
            'password' => 'required|string|min:8|confirmed'
        ]);
        
        $data = $request->all();
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid'])->withInput();
        }
        // echo "<pre>"; print_r($data); exit;
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->type = 1;
        $user->is_verified = 1;
        $user->show_my_gender = 1;
        if($user->save()) {
            //trigger AssignDefaultPlan event
            $eventData = ['platform' =>2,'plan_id'=>1,'transaction_id'=>0,'product_id'=>'0'];
            event(new AssignDefaultPlan($user,$eventData));

            return redirect('users')->with(['status' => 'success', 'message' => 'User Saved Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    
    }

    function editUser($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        $user = User::where('id', $id)->first();
        if(!$user) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        return view('admin.users.edit', compact(['user']));
    }

    function updateUser($id = null, Request $request) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Please select a user.']);
        }
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'sometimes|confirmed'
        ]);
        
        $data = $request->all();
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid'])->withInput();
        }
        $user = User::find($id);
        if(!$user) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        $user->name = $data['name'];
        $user->email = $data['email'];
        if($data['password'] != "") {
            $user->password = bcrypt($data['password']);
        }

        if($user->save()) {
            // return redirect()->back()->with(['status' => 'success', 'message' => 'User updated successfully']);
            if( $user->type == 1 )
                return redirect('/users')->with(['status' => 'success', 'message' => 'User updated successfully']);
            
            return redirect('/admins')->with(['status' => 'success', 'message' => 'User updated successfully']);
            
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    function deleteUser($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Please select a user.']);
        }
        
        Feedback::where('user_id',$id)->delete();
        UserTopPickup::where('user_id',$id)->delete();
        UserImage::where('user_id',$id)->delete();
        Log::where('user_id',$id)->delete();
        ChatMessage::where('user_id',$id)->delete();
        DeviceToken::where('user_id',$id)->delete();
        SubscriptionReceipts::where('user_id',$id)->delete();
        ConsumableFeatures::where('user_id',$id)->delete();
        $subscriptionIds = Subscriptions::where('user_id',$id)->pluck("id");
        SubscriptionFeaturesStatus::whereIn('subscription_id',$subscriptionIds)->delete();
        LikeDislike::where('sender_id', $id)->orWhere('receiver_id', $id)->delete();
        Match::with(['thread_messages'])->where('person_1', $id)->orWhere('person_2', $id)->delete();
        Subscriptions::where('user_id',$id)->delete();
        
        if(User::where('id', $id)->delete()) {
            return redirect()->back()->with(['status' => 'success', 'message' => 'User deleted successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.']);
        }
    }
    function userLocation(Request $request){
        $locations = UserAddress::groupBy('city')->selectRaw('count(*) as total, city')->get()->toArray();
        // echo "<pre>";print_r($locations);die;
        return view('admin.users.location', compact('locations'));

    }

    function userProfile($id = null) {
        if($id == null) { 
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        $user = User::with('user_images')->where('id', $id)->first();
        $logs = Log::where('user_id', $id)->orderBy('id', 'DESC')->get();
        if(!$user) { 
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        if($user->interests){
            $interest = explode(',',$user->interests);
            $title = Interest::whereIn('id', $interest)->pluck('title')->toArray();
            $user->user_interest = implode(',', $title);
        }else{
            $user->user_interest = "";
        }
        
        // echo "<pre>"; print_r($user->toArray()); exit;
        return view('admin.users.profile', compact(['user', 'logs']));
    }

    function download_user_csv($filter = null) {
        return Excel::download(new UsersExport($filter), 'users_'.time().'.csv');
        // return redirect()->back();
    }


    public function verify_email($token) {
        $user = User::where('email_verification_token', $token)->first();
        if($user) {
            $res = User::where('email_verification_token', $token)->update(['is_verified' => 1]);
            if($res) {
                $data = ['success' => true, 'message' => "Email verified Successfullt"];
            }   else {
                $data = ['success' => true, 'message' => "Couldn't verify the email. Please try again."];
            } 
        }   else {
            $data = ['success' => false, 'message' => "Invalid token"];
        }
        return view('admin.email_verify_result', compact(['data']));
    }

    public function listReportReasons() {
        $reasons = ReportReason::orderBy('id', 'DESC')->get()->toArray();
        return view('admin.reasons.list', compact(['reasons']));
    }

    function addReason() {
        return view('admin.reasons.add');
    }


    function saveReason(Request $request) {
        $validatedData = $request->validate([
            'reason_text' => 'required',
            'ge_reason_text' => 'required',
            'tr_reason_text' => 'required'
        ]);
        
        $data = $request->all();

        $reason = new ReportReason();
        $reason->reason_text_en = $data['reason_text'];
        $reason->reason_text_de = $data['ge_reason_text'];
        $reason->reason_text_tr = $data['tr_reason_text'];
        $reason->save();

        if($reason->save()) { 
            return redirect('reason/list')->with(['status' => 'success', 'message' => 'Reason Saved Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    function edit_reason($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid reason id']);
        }
        $reason = ReportReason::find($id);
        if(!$reason) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Reason not found']);
        } 
        return view('admin.reasons.edit', compact(['reason']));
    }

    function updateReason(Request $request, $id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid reason id']);
        }
        $validatedData = $request->validate([
            'reason_text' => 'required',
            'ge_reason_text' => 'required',
            'tr_reason_text' => 'required'
        ]); 
        $reason = ReportReason::find($id);
        if(!$reason) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid reason id']);
        } 

        $data = $request->all();
        $reason->reason_text_en = $data['reason_text'];
        $reason->reason_text_de = $data['ge_reason_text'];
        $reason->reason_text_tr = $data['tr_reason_text'];
        if($reason->save()) {
            return redirect('/reason/list')->with(['status' => 'success', 'message' => 'Reason updated Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    function deleteReason($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid reason id']);
        }
        $reason = ReportReason::find($id);
        if(!$reason) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Reason not found']);
        } 
        $res = ReportReason::where('id', $id)->delete();
        if($res) {
            return redirect()->back()->with(['status' => 'success', 'message' => 'Reason deleted Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    function list_admins() { 
        $users = User::where('type', 3)->orderBy('id', 'DESC')->get();
        return view('admin.admins.list', compact(['users']));
    }

    function add_admin() {
        return view('admin.admins.add');
    }

    function saveAdmin(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);
        
        $data = $request->all();
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Email Address is invalid'])->withInput();
        }
        // echo "<pre>"; print_r($data); exit;
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);
        $user->type = 3;
        $user->is_verified = 1;
        if($user->save()) {
            return redirect('admins')->with(['status' => 'success', 'message' => 'User Saved Successfully']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something Went wrong.'])->withInput();
        }
    }

    function editAdmin($id = null) {
        if($id == null) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        $user = User::where('id', $id)->first();
        if(!$user) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid user id']);
        }
        return view('admin.admins.edit', compact(['user']));
    }

    function userFeedback(Request $request) { 
        $feedbacks = Feedback::with('feedbackUser')->orderBy('id', 'DESC')->paginate(10);
        //print'<pre>';print_R($feedbacks->toArray());exit;
        return view('admin.feedback.list', compact(['feedbacks']));
    }

}
