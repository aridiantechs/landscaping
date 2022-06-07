<?php

namespace App\Services;

use App\Models\Notification as NotificationModel;

class NotificationService{

	public static function send($user_devices,$data = array(),$push_notify=true){
        
        if (is_array($data['to_user_id'])) {
            //loop notification
            foreach ($data['to_user_id'] as $key => $to_user) {
                $new_data = $data;
                $new_data['to_user_id'] = $to_user;
                self::app_notification($new_data);
            }
        } else {
            self::app_notification($data);
        }

        if($push_notify){
            self::push_notification($user_devices,$data);
        }

        return true;
	}

    //push notification function
    public static function push_notification($user_devices,$data = array()){

        if(!$user_devices){
            return false;
        }
        
        // $data=collect($data)->only(['title','body','req_id']);

        return fcm()
            ->to($user_devices)
            ->priority('high')
            ->timeToLive(0)
            ->data($data)->notification($data)
           ->send();

    }

    //app notifcation function
    public static function app_notification($data = array()){
        $noti = NotificationModel::create([
            'type' => $data['type'] ?? null,
            'to_user_id' => $data['to_user_id'] ?? null,
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'req_id' => $data['req_id'] ?? null,
            'object' => $data['object'] ?? null,
        ]);
    }

}