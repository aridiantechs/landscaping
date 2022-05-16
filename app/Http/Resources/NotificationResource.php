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
        return [
            "id"      => $this->id ?? '',
            "type"    => $this->type ?? '',
            "user_id" => $this->form_user_id ?? '',
            "title"   => $this->title ?? '',
            "body"    => $this->body ?? '',
            "object"  => $this->object ?? '',
            "seen"    => $this->seen ?? '',	
        ];
    }
}
