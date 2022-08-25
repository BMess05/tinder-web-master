<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;


class PolicyAndTermsController extends Controller
{
    
    public function policy(Request $request,$lang){
        if($lang=='en'){
            return view('policy.privacy_policy');
        }else if($lang=='de'){
            return view('policy.privacy_policy_ge');
        }else if($lang=='tr'){
            return view('policy.privacy_policy_tr');
        }else{
            $ret = array('success'=>0, 'message'=> 'Invalid Request');
            return response()->json($ret); 
        }
    }

    public function terms(Request $request,$lang){
        if($lang=='en'){
            return view('termsnconditions.terms_of_use');
        }else if($lang=='de'){
            return view('termsnconditions.terms_of_use_ge');
        }else if($lang=='tr'){
            return view('termsnconditions.terms_of_use_tr');
        }else{
            $ret = array('success'=>0, 'message'=> 'Invalid Request');
            return response()->json($ret); 
        }
    }

}
