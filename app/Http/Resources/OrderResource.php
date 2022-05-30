<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'order_id' => $this->uuid,
            'user' => $this->user->name,
            'city'=> $this->city,
            'state'=> $this->state,
            'country'=> $this->country,
            'lat'=>$this->lat,
            'lng'=>$this->lng,
            'full_address'=>$this->full_address,
            'status' => $this->order_status->status ?? 'PENDING',
            'worker'=> $this->order_status && $this->order_status->worker ?$this->order_status->worker()->get([
                'id',
                'first_name',
                'last_name',
                'phone',
                'email',
                'email_verified_at',
                'photo_path'
            ]) : [],
            'enable_action' => $this->when(auth()->user()->hasRole('endUser') && $this->order_area()->exists() && $this->order_area->customer_response == 'PENDING', function () {
                return true;
            }, function () {
                return false;
            }),
        ];
    }
}
