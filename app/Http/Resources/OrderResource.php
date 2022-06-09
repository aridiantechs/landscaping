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

        if ($this->order_status) {
            if ($this->order_status->status == 'PENDING') {
                $o_status = 'ACCEPTED';
            } else {
                $o_status = $this->order_status->status;
            }
            
        } else {
            $o_status = 'PENDING';
        }

        if ($this->order_status && $this->order_status->worker) {
            $worker = $this->order_status->worker()->select(
                'id',
                'first_name',
                'last_name',
                'phone',
                'email',
                'email_verified_at',
                'photo_path'
            )->first();
        } else {
            $worker = (object)[];
        }
        
        
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
            'status' => $o_status,
            'schedule_data'=>$this->accepted_schedule_response && $this->schedule_response ? $this->schedule_data() : null,
            'worker'=>  $this->when(auth()->user()->hasRole('endUser'), $worker),
            'enable_action' => $this->when(auth()->user()->hasRole('endUser') && $this->order_area()->exists() && $this->order_area->customer_response == 'PENDING', function () {
                return true;
            }),
        ];
    }
}
