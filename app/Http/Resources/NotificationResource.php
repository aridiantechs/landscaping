<?php

namespace App\Http\Resources;

use Carbon\Carbon;
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
        if ($this->order && !$this->order->accepted_response && !$this->order->accepted_schedule_response) {
            $type='new_request';
        } else {
            $type='old_request';
        }
        

        return [
            'id' => (string)$this->id ?? "",
            'type' => (string)$type,
            'order' => new OrderResource($this->order),
            'title' => (string)$this->title ?? "",
            'body' => (string)$this->body ?? "",
            'object' => (string)$this->object ?? "",
            'date' => (string)Carbon::parse($this->created_at)->format('d-m-Y') ?? "",
            'time' => (string)Carbon::parse($this->created_at)->format('h:i A') ?? "",
            'seen' => (string)$this->seen ?? "",
        ];
    }
}
