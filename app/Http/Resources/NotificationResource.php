<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        // return [
        //     "id"      => (string)$this->id ?? '',
        //     "type"    => (string)$this->type ?? '',
        //     "user_id" => (string)$this->form_user_id ?? '',
        //     "title"   => (string)$this->title ?? '',
        //     "body"    => (string)$this->body ?? '',
        //     "object"  => (string)$this->object ?? '',
        //     "seen"    => (string)$this->seen ?? '',	
        // ];

        $action = "";
        if($this->object){
            if(array_key_exists('action_status', $this->object))
            $action = $this->object['action_status'];
        }

        return [
            'id' => (string)$this->id ?? "",
            'type' => (string)$this->type ?? "",
            'user_id' => (string)$this->user_id ?? "",
            'title' => (string)$this->title ?? "",
            'body' => (string)$this->body ?? "",
            'object' => (string)$this->object ?? "",
            'date' => (string)dateToTimezone($this->created_at,'m-d-Y') ?? "",
            'time' => (string)dateToTimezone($this->created_at,'h:i A') ?? "",
            'seen' => (string)$this->seen ?? "",
            'action' => (string)$action ?? "",
        ];
    }
}
