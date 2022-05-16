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
            'worker'=>$this->when(auth()->user()->hasRole('endUser') && $this->order_status()->exists(), function () {
                return $this->order_status->worker;
            }),
        ];
    }
}
