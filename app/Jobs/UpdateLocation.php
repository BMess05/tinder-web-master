<?php

namespace App\Jobs;
use App\Model\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Model\UserAddress;
class UpdateLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $user_id;
    protected $lat;
    protected $long;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $lat, $long)
    {
        $this->user_id = $user_id;
        $this->lat = $lat;
        $this->long = $long;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    { 
        // $geocode=file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?latlng='.$this->lat.','.$this->long.'&sensor=false&key='.env('GMAP_KEY'));

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$this->lat.','.$this->long.'&sensor=false&key='.env('GMAP_KEY');
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $geocode = curl_exec($curl);

        curl_close($curl);
        // echo $geocode;
        // die;



        $output= json_decode($geocode);
        // echo "<pre>";print_r($output);die;

        // dd($output);
        //print'<pre>';print_r($output->results[0]->address_components); exit; 
        //$output->results[0]->formatted_address

        $city = ""; 
        $postal = "";
        $city_name = "";
        if(isset($output->results[0]->address_components)) {
            foreach($output->results[0]->address_components as $compo) {
                /*if(isset($compo->types[0]) && ($compo->types[0] == "administrative_area_level_2")) {
                    $city .= $compo->long_name;
                }
                if(isset($compo->types[0]) && ($compo->types[0] == "administrative_area_level_1")) {
                    $city .= ", ".$compo->long_name;
                } */
                if(isset($compo->types[0]) && ($compo->types[0] == "locality")) {
                    $city .= $compo->long_name;
                    $city_name = $compo->long_name;
                }
                if(isset($compo->types[0]) && ($compo->types[0] == "country")) {
                    $city .= ", ".$compo->long_name;
                    $country = $compo->long_name;
                }
                if(isset($compo->types[0]) && ($compo->types[0] == "postal_code")) {
                    $postal = $compo->long_name;
                }
            }
        }
        $cityDetails = "";
        if($postal!=""){
            $cityDetails = trim($postal).' '.trim($city,",");
        }else{
            $cityDetails = trim($city,",");
        }
       
        $user_address = UserAddress::where('user_id', '=', $this->user_id)->first();
        if ($user_address === null && $city_name !='') {
            
            $userAddress = new UserAddress();
            $userAddress->latitude = $this->lat;
            $userAddress->longitude =$this->long;
            $userAddress->city = $city_name;
            $userAddress->country = $country;
            $userAddress->user_id = $this->user_id;
            $result = $userAddress->save();
        }

        // echo $city_name;echo "---";echo $country; exit;
        $user = User::find($this->user_id);

        $user->latitude = $this->lat;
        $user->longitude = $this->long;
        $user->city = $cityDetails;
        $user->save();
        
    }
}
