<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Advertisement;
use App\Model\AdPreference;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AdvertisementRequest;

class AdvertisementController extends Controller
{
    protected $user;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user= \Auth::user();
            if(auth('companies')->user()->subscription == 0) {
                return redirect()->back()->with(['status' => 'danger', 'message' => 'You need to purchase subscription in order to add advertisements.']);
            }
            return $next($request);
        });
    }

    public function index() {
        $advertisements = Advertisement::where('company_id', auth('companies')->user()->id)->orderBy('id', 'DESC')->get();
        return view('companies.advertisements.list', compact('advertisements'));
    }

    public function add() {
        // echo auth('companies')->user()->company_name; die;
        return view('companies.advertisements.add');
    }

    public function save(AdvertisementRequest $request) {
        $data = $request->all();
        
        DB::beginTransaction();
        $adv = new Advertisement();
        $adv->title = $data['title'];
        $adv->description = $data['description'];
        $adv->url = $data['url'];
        $adv->company_id = auth('companies')->user()->id;
        if(isset($data['cropped_image_name'])) {
            $folderPath = public_path('uploads/adds/');
            if (!file_exists($folderPath)) { 
                mkdir($folderPath, 0777, true);
            }
            $image_parts = explode(";base64,", $data['cropped_image_name']);
            $image_type_aux = explode("image/", $image_parts[0]);
            
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $imageName = uniqid() . date('YmdHis') . '.png';
            $imageFullPath = $folderPath.$imageName;
            file_put_contents($imageFullPath, $image_base64);
            $adv->image = $imageName;
        }
        if($adv->save()) {
            $ad_pref = new AdPreference();
            $ad_pref->ad_id = $adv->id;
            $ad_pref->latitude = $data['lat'];
            $ad_pref->longitude = $data['long'];
            $ad_pref->age_from = $data['age_from'];
            $ad_pref->age_to = $data['age_to'];
            $ad_pref->gender_group = implode(',', $data['gender_group']);
            $ad_pref->address = $data['preferred_location'];
            $ad_pref->diameter = $data['diameter'];
            if($ad_pref->save()) {
                DB::commit();
                return redirect()->route('listAdvertisements')->with(['status' => 'success', 'message' => 'Advertisement Saved Successfully.']);
            }
        }
        DB::rollback();
        return redirect()->back()->with(['status' => 'danger', 'message' => 'Something went wrong, please try again.']);
        
    }

    public function edit($id) {
        $adv = Advertisement::find($id);
        // dd($adv->ad_preference->toArray());
        
        if(!$adv) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid advertisement ID.']);
        }
        return view('companies.advertisements.edit', compact('adv'));
    }

    public function update($id, AdvertisementRequest $request) {
        $data = $request->all();
        // dd($data);
        DB::beginTransaction();
        $adv = Advertisement::find($id);
        if(!$adv) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid advertisement ID.']);
        }
        if($adv->company_id != auth('companies')->user()->id) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Unauthorized actions.']);
        }
        $adv->title = $data['title'];
        $adv->description = $data['description'];
        $adv->url = $data['url'];
        
        if(isset($data['cropped_image_name'])) {
            $folderPath = public_path('uploads/adds/');
            if (!file_exists($folderPath)) { 
                mkdir($folderPath, 0777, true);
            }
            $image_parts = explode(";base64,", $data['cropped_image_name']);
            $image_type_aux = explode("image/", $image_parts[0]);
            
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $imageName = uniqid() . date('YmdHis') . '.png';
            $imageFullPath = $folderPath.$imageName;
            file_put_contents($imageFullPath, $image_base64);
            $adv->image = $imageName;
        }
        if($adv->save()) {
            $ad_pref = AdPreference::where('ad_id', $adv->id)->first();
            if(!$ad_pref) {
                $ad_pref = new AdPreference();
            }
            
            $ad_pref->ad_id = $adv->id;
            $ad_pref->latitude = $data['lat'];
            $ad_pref->longitude = $data['long'];
            $ad_pref->age_from = $data['age_from'];
            $ad_pref->age_to = $data['age_to'];
            $ad_pref->gender_group = implode(',', $data['gender_group']);
            $ad_pref->address = $data['preferred_location'];
            $ad_pref->diameter = $data['diameter'];
            if($ad_pref->save()) {
                DB::commit();
                return redirect()->route('listAdvertisements')->with(['status' => 'success', 'message' => 'Advertisement Updated Successfully.']);
            }
        }
        DB::rollback();
        return redirect()->back()->with(['status' => 'danger', 'message' => 'Something went wrong, please try again.']);
    }

    public function delete($id) {
        $adv = Advertisement::find($id);
        if(!$adv) {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Invalid advertisement ID.']);
        }
        if($adv->delete()) {
            AdPreference::where('ad_id', $adv->id)->delete();
            return redirect()->route('listAdvertisements')->with(['status' => 'success', 'message' => 'Advertisement Deleted Successfully.']);
        }   else {
            return redirect()->back()->with(['status' => 'danger', 'message' => 'Something went wrong, please try again.']);
        }
    }

    public function activeInactiveAdd(Request $request) {
        $data = $request->all();
        $adv = Advertisement::find($data['id']);
        if($adv && (\Auth::user()->role_id == 0)) {
            if($adv->is_active == 0) {
                $adv->is_active = 1;
                $msg = "Advertisement set to active.";
            }   else {
                $adv->is_active = 0;
                $msg = "Advertisement set to inactive.";
            }
            if($adv->save()) {
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
        }   else {
            return response()->json([
                "success" => 0,
                "message" => 'Invalid input.'
            ]);
        }
    }
}
