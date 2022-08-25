<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Model\Advertisement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Model\AdHistory;

class AdvertisementController extends Controller
{
    public function listAdds() {
        $user = auth()->user();
        $latitude = $user->latitude;
        $longitude = $user->longitude;
        $age = 0;
        if($user->dob != "") {
            $date = new \DateTime();
            $dob = new \DateTime($user->dob);
            $ageObj = $dob->diff($date);
            $age = $ageObj->y;
        }
        $gender = $user->gender ?? 0;
        
        // dd($user->gender);
        $history = AdHistory::where('user_id', auth()->user()->id)->pluck('ad_id');
        $history_ads = $history->toArray();
        //  To search by kilometers instead of miles, replace 3959 with 6371.
        $adResult = Advertisement::join('ad_preferences', 'advertisements.id', '=', 'ad_preferences.ad_id')
            ->selectRaw('*, advertisements.id as advertisement_id, ( 6371 * acos( cos( radians('.$latitude.') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('.$longitude.') ) + sin( radians('.$latitude.') ) * sin( radians( latitude ) ) ) ) AS distance')
            ->join('companies', 'advertisements.company_id', '=', 'companies.id')
            ->where('companies.subscription', 1)
            ->where('latitude', '!=', null)
            ->where('longitude', '!=', null)
            ->where('is_active', 1)
            ->where('age_from', '<=', $age)
            ->where('age_to', '>=', $age)
            ->whereNotIn('ad_id', $history_ads)
            ->where('gender_group', 'LIKE', '%'.$user->gender.'%')
            // ->having('distance', '<=', 'diameter')
            ->orderBy('distance', 'ASC')
            ->get();

        // echo "<pre>"; print_r($adResult->toArray()); die;
        if($adResult) {

            $allAds = $adResult->toArray();
            $resAds = [];
            foreach($allAds as $ad) {
                if($ad['distance'] < $ad['diameter']) {
                    $resAds[] = $ad;
                }
            }
            $res = ['success' => true, 'message' => __('messages.ads_found'), 'data' => $resAds];
        }   else {
            $res = ['success' => false, 'message' => __('messages.no_ad_found')];
        }
        return response()->json($res,200,[],JSON_NUMERIC_CHECK);
    }

    public function ad_seen(Request $request) {
        $validator = Validator::make($request->all(), [
            'ad_id' => 'required|exists:advertisements,id'
        ]);
        if ($validator->fails()) {
            $res = [
                'success' => false,
                'message' => $validator->messages()->first()
            ];
            return response()->json($res);
        }

        $data = $request->all();
        $user_id = auth()->user()->id;
        $ad_history = AdHistory::where('user_id', $user_id)->where('ad_id', $data['ad_id'])->first();
        if(!$ad_history) {
            $ad_history = new AdHistory();
            $ad_history->ad_id = $data['ad_id'];
            $ad_history->user_id = $user_id;
            $ad_history->seen_count = 1;
        }   else {
            $last_seen_count = $ad_history->seen_count;
            $ad_history->seen_count = $last_seen_count + 1;
        }
        $ad_history->last_seen = date('Y-m-d H:i:s');
        if($ad_history->save()) {
            $res = ['success' => true, 'message' => __('messages.ad_seen_successfully')];
            $code = 200;
        }   else {
            $res = ['success' => false, 'message' => __('messages.something_went_wrong')];
            $code = 500;
        }
        return response()->json($res,$code, [], JSON_NUMERIC_CHECK);
    }
}
