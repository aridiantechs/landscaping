<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\UserDevice;
use App\Models\Subscription;
use Laravel\Sanctum\HasApiTokens;
use App\Models\SquareCustomerCard;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Square\Models\Location;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes,HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'square_customer_id',
        'first_name', 
        'last_name', 
        'email', 
        'phone', 
        'date_of_birth',
        'gender',
        'nationality',
        'password',
        'otp',
        'otp_expiry',
        'otp_verified_at',
        'profile_image',
        'photo_path',
        'provider'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'owner' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function getPhotoPathAttribute($value)
    {
        return $value ? ($this->provider ? $value : (url('/').'/storage/uploads/users/' . $value) ): "";
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    //square card
    public function square_card()
    {
        return $this->hasOne(SquareCustomerCard::class);
    }

    // all subscriptions
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class,'customer_id','square_customer_id');
    }

    // latest subscriptions
    public function lastSubscription()
    {
        return $this->hasOne(Subscription::class,'customer_id','square_customer_id')->latest()->first();
    }
    
    // active subscription
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class,'customer_id','square_customer_id')->latest()->whereDate('end_date', '>=', now())->where('status','ACTIVE');
    }

    // subscriptions where 1 day have passed since the end date
    public function dayOldSubscription()
    {
        $sub=$this->subscriptions()->latest()->first();
        if ($sub && Carbon::parse($sub->end_date)->diffInDays(now()) == 1) {
            return $sub;
        }
        return false;
    }

    // subscriptions trial has ended
    public function trialEnded()
    {
        $sub=$this->subscriptions()->latest()->first();
        if ($sub && $sub->trial_end_at && Carbon::parse($sub->trial_end_at)->lt(now()) && $sub->status !='ACTIVE' ) {
            return $sub;
        }
        return false;
    }

    public function trialEndedOrNoSubscription()
    {
        $sub=$this->subscriptions()->latest()->first();
        if(!$sub){
            return true;
        }
        if ($sub && $sub->trial_end_at && Carbon::parse($sub->trial_end_at)->lt(now()) && $sub->status !='ACTIVE' ) {
            return $sub;
        }
        return false;
    }

    public function getNameAttribute()
    {
        return $this->first_name.' '.$this->last_name;
    }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::needsRehash($password) ? Hash::make($password) : $password;
    }

    public function isDemoUser()
    {
        return $this->email === 'johndoe@example.com';
    }

    public function location()
    {
        return $this->hasOne(GeoLocation::class,'user_id','id')->latest();
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('last_name')->orderBy('first_name');
    }

    public function scopeWhereRole($query, $role)
    {
        switch ($role) {
            case 'user': return $query->where('owner', false);
            case 'owner': return $query->where('owner', true);
        }
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        })->when($filters['role'] ?? null, function ($query, $role) {
            $query->whereRole($role);
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    // if user has already accepted any order
    public function hasAcceptedOrder()
    {
        $orders= Order::whereHas('order_responses',function($q){
                    $q->where('user_id',auth()->user()->id)->where('response_type','ACCEPTED');
                })->whereHas('order_area',function($q){
                    $q->where('worker_id',auth()->user()->id)->where('customer_response','ACCEPTED');
                })->whereHas('order_status',function($q){
                    $q->where('worker_id',auth()->user()->id)->where('status','PENDING');
                })->count();
        
        if ($orders) {
            return true;
        } else {
            return false;
        }
        
    }
    

}
