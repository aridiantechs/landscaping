<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if(!empty($this->email_verified_at)){
            $email_verified = true;
        }else{
            $email_verified = false;
        }
        
        if($this->hasRole('superadmin')){
            $role = 'super_admin';
        }elseif($this->hasRole('endUser')){
            $role = 'endUser';
        }else{
            $role = 'worker';
        }
        return [
            "id" => $this->id ?? "",
            "square_customer_id" => $this->square_customer_id ?? "",
            "first_name" =>$this->first_name ?? "",
            "last_name" =>$this->last_name ?? "",
            "phone" => $this->phone ?? "",
            "email" => $this->email ?? "",
            "profile_image" => $this->profile_image ?? "",
            "role" => $role ?? "",
            // "company_profile" => $this->company_profile ?? null,
            "email_verified" => $email_verified ?? false,
            'has_active_subscription' => $this->activeSubscription ? true : false,
        ];
        return parent::toArray($request);
    }
}
