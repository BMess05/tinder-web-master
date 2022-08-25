<?php

namespace App;

use Illuminate\Http\Request;

use App\Model\User;
use App\Model\DeviceToken;
use Auth,Exception,DB;

// use Edujugon\PushNotification\PushNotification;
use Illuminate\Support\Facades\Log;

class FirebaseHelper
{
    private $push;
    private $fcm_key;
    public function __construct()
    {
        // $this->push = new PushNotification('fcm');
        // $this->push->setConfig([
        //     'priority' => 'high',
        //     'time_to_live' => 3
        // ]);
        // // $this->push->setApiKey( config('tundur.FIREBASE_SERVER_KEY') );
        // $this->push->setApiKey( 'AAAAhETRNSo:APA91bFgnyfPISg3_vbpNjbOesdLJ3zHJgCxwRpZ-OINXaJvwtGj-JlarAtSm3_yp3AN88m7ppSJi9Ny_ZkNGwA9LonDdmfqtY19A6PgP3xvp3J3D87MSNpJJk2WaiE6xpdmM_6dDa14' );


        $this->fcm_key = "AAAAhETRNSo:APA91bFgnyfPISg3_vbpNjbOesdLJ3zHJgCxwRpZ-OINXaJvwtGj-JlarAtSm3_yp3AN88m7ppSJi9Ny_ZkNGwA9LonDdmfqtY19A6PgP3xvp3J3D87MSNpJJk2WaiE6xpdmM_6dDa14";


        // dd( config('tundur.FIREBASE_SERVER_KEY')  );
    }

    public function chatMessageReceived( $data, $thread_data,$sender_image,$total_unread,$notify_id )
    {
        
        $title = __('pushnotification.chat_message_received');
        $body = __('pushnotification.chat_message_received_body');

        $notifyTo =User::find($notify_id)->load('user_device_tokens');
        $device_tokenss = $notifyTo->user_device_tokens()->pluck('device_token')->toArray();
        $sender = User::find($data->user_id)->toArray();

        $serverKey = $this->fcm_key;

        $url = "https://fcm.googleapis.com/fcm/send";
        // $token = $device_tokenss[1];
        // dd($device_tokenss);
        foreach($device_tokenss as $token) {
            $notification = [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
                'mutable-content'=> 1,
                'content-available'=> 1,
                'badge' => (int) $total_unread
            ];
            $arrayToSend = array(
                'to' => $token,
                'notification' => $notification,
                'priority' => 'high',
                'data' => [
                    'notification_type' =>'chat_message',
                    'sender_id' =>(int) $sender['id'],
                    'sender_name' =>$sender['name'],
                    'thread_id' =>$thread_data->thread_id,
                    'total_unread' =>(int) $total_unread,
                    'sender_image' =>$sender_image,
                ]
            );
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key=' . $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            $response = curl_exec($ch);
            if ($response == false) {
                $result_noti = 0;
            } else {
                $result_noti = 1;
            }
            curl_close($ch);
            // dd($response);
        }
        
        return $result_noti;

    }


    public function matchFound( $data, $notify_id )
    {
        $user = \Auth::user();
        $sender = User::find($data['user_id'])->toArray();
        $receiver = User::find($notify_id)->toArray();

        $lang = $receiver['content_language'] != '' ? $receiver['content_language'] : 'en';
        app()->setlocale($lang);
        
        $title = __('pushnotification.match_found');
        $body = __('pushnotification.match_found_body');
        $notifyTo =User::find($notify_id)->load('user_device_tokens');
        $device_tokenss = $notifyTo->user_device_tokens()->pluck('device_token')->toArray();
        $serverKey = $this->fcm_key;

        $result_noti = 0;
        $url = "https://fcm.googleapis.com/fcm/send";
        foreach($device_tokenss as $token) {
            $notification = [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
                'mutable-content'=> 1,
                'content-available'=> 1,
            ];
            $arrayToSend = array(
                'to' => $token,
                'notification' => $notification,
                'priority' => 'high',
                'data' => [
                    'notification_type' =>'match',
                    'sender_id' =>(int) $sender['id'],
                    'sender_name' =>$sender['name'],
                    'thread_id' =>(int) $data['thread_id']
                ]
            );
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key=' . $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            $response = curl_exec($ch);
            if ($response == false) {
                $result_noti = 0;
            } else {
                $result_noti = 1;
            }
            curl_close($ch);
        }
        return $result_noti;

    }

    public function likeSuperLikeNotification( $data, $notify_id )
    {
        // try {
        //     $user = \Auth::user();

        //     $sender = User::find($data['user_id'])->toArray();
        //     $receiver = User::find($notify_id)->toArray();

        //     $lang = $receiver['content_language'] != '' ? $receiver['content_language'] : 'en';
        //     app()->setlocale($lang);

            // if($data['notificationType'] == 'like'){
            //     $title = $sender['name'].' '.__('pushnotification.likes_you');
            //     $body = $sender['name'].' '.__('pushnotification.likes_you_body');
            // }else{
            //     $title = $sender['name'].' '.__('pushnotification.super_likes_you');
            //     $body  = $sender['name'].' '.__('pushnotification.super_likes_you_body');
            // }

        //     $notifyTo =User::find($notify_id)->load('user_device_tokens');
        //     $notificationData = [
                                    // 'title' => $title,
                                    // 'body'  => $body,
                                    // 'sound' => 'default',
                                    // 'mutable-content'=> 1,
                                    // 'content-available'=> 1
        //                             // 'badge' => (int) $badge_count
        //                         ];

        //     $extraNotificationData = [
        //             'notification_type' => $data['notificationType'],
        //             'sender_id' =>(int) $sender['id'],
        //             'sender_name' =>$sender['name']
        //     ];

        //     // dd($notificationData, $extraNotificationData);


        //     $this->push->setMessage([
        //             'notification' => $notificationData,
        //             'data' =>$extraNotificationData
        //     ]);

        //     $this->push->setDevicesToken($notifyTo->user_device_tokens()->pluck('device_token')->toArray());
            
        //     $this->push->send();

        //     //check for invalid devicetokens
        //     $unregistered = $this->push->getUnregisteredDeviceTokens();

        //     //remove invalid devicetokens
        //     if( !empty($unregistered) ){
        //         $this->removeInvalidDeviceToken($unregistered);
        //     }
        //     $result = $this->push->getFeedback();
        //     Log::info(json_encode($result));
        //     //return true;
        // }
        // catch( \Exception $e){
        //     Log::error($e->getMessage());
        //     //dump($e->getMessage(),1);
        // }






        $notifyTo =User::find($notify_id)->load('user_device_tokens');
        $device_tokenss = $notifyTo->user_device_tokens()->pluck('device_token')->toArray();
        $sender = User::find($data['user_id'])->toArray();

        if($data['notificationType'] == 'like'){
            $title = $sender['name'].' '.__('pushnotification.likes_you');
            $body = $sender['name'].' '.__('pushnotification.likes_you_body');
        }else{
            $title = $sender['name'].' '.__('pushnotification.super_likes_you');
            $body  = $sender['name'].' '.__('pushnotification.super_likes_you_body');
        }

        $serverKey = $this->fcm_key;

        $url = "https://fcm.googleapis.com/fcm/send";
        // $token = $device_tokenss[1];
        // dd($device_tokenss);
        $result_noti = 0;
        foreach($device_tokenss as $token) {
            $notification = [
                'title' => $title,
                'body'  => $body,
                'sound' => 'default',
                'mutable-content'=> 1,
                'content-available'=> 1
            ];
            $arrayToSend = array(
                'to' => $token,
                'notification' => $notification,
                'priority' => 'high',
                'data' => [
                    'notification_type' => $data['notificationType'],
                    'sender_id' =>(int) $sender['id'],
                    'sender_name' =>$sender['name']
                ]
            );
            $json = json_encode($arrayToSend);
            $headers = array();
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: key=' . $serverKey;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
            $response = curl_exec($ch);
            if ($response == false) {
                $result_noti = 0;
            } else {
                $result_noti = 1;
            }
            curl_close($ch);
            // dd($response);
        }
        
        return $result_noti;

    }



    public function removeInvalidDeviceToken( $tokens )
    {
        try{
            DeviceToken::whereIn('device_token', $tokens)->delete();
        }
        catch( \Exception $e)
        {
            Log::info($e->getMessage());
        }
    }
    

    private function getbadgeCount( $user_id )
    {   
        // try
        // {
        //     $pending = Connects::where('status','=',1)
        //                             ->where('receiver_id','=',$user_id)
        //                              ->reject(function ($connect) {
        //                                     return $connect->is_blocked || $connect->is_reported || !$connect->is_active;
        //                                  })
        //                             ->count()
        //                             ;

        //     $unreadChats = UsersChats::where('receiver_id',$user_id)
        //                                 ->where('is_read',0)
        //                                 ->get()
        //                                 ->reject(function ($chat) {
        //                                     return ($chat->project_count == 0 && $chat->connection_status == 0) //no project and no connection
        //                                             || $chat->sender_blocked  //user is blocked
        //                                             || $chat->sender_reported  //user is reported
        //                                             ;
        //                                  })
        //                                 ->count()
        //                                 ;
            
        //      if( \DB::table('project_list_viewed_time')->where('user_id',$user_id)->exists() ){
        //             $projects = Projects::without('other_user_data','chat_data','milestones','events')
        //                         ->leftJoin('project_list_viewed_time','projects.connected_user_id', '=', 'project_list_viewed_time.user_id')
        //                         ->where( 'projects.connected_user_id', $user_id )
        //                         ->where( 'project_list_viewed_time.user_id', $user_id)
        //                         ->whereRaw('project_list_viewed_time.last_seen_time <= projects.created_at')
        //                         ->select('projects.id')
        //                         ->count()
        //                         ;
        //     }else{
        //             $projects = Projects::without('other_user_data','chat_data','milestones','events')
        //                                 ->where( 'projects.connected_user_id', $user_id )
        //                                 ->select('projects.id')
        //                                 ->count()
        //                                 ;
        //     }

        //    return (int) ($pending + $projects + $unreadChats);
        // }
        // catch( Exception $e)
        // {
        //     Log::error($e->getMessage());
        //     return $e->getMessage();
        // }       

    }

}
