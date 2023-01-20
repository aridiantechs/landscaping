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
            "profile_image" => $this->photo_path ?? "",
            "role" => $role ?? "",
            "provider" => $this->provider ?? "",
            // "company_profile" => $this->company_profile ?? null,
            "email_verified" => $email_verified ?? false,
            // temporary removal of square
            // 'has_active_subscription' => $this->activeSubscription() || $this->dayOldSubscription() ? true : false,
            // 'trial_ended' => $this->trialEnded()

            // temporary removal of square
            'has_active_subscription' =>true,
            'trial_ended' => false
        ];
        return parent::toArray($request);
    }
}
