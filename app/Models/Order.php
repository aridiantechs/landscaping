<?php

namespace App\Models;

use App\Models\User;
use App\Models\OrderArea;
use App\Models\OrderResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable=[
        'uuid',
        'user_id',
        'city',
        'state',
        'country',
        'lat',
        'lng',
        'full_address',
    ];


    public function scopeListing($query)
    {
        if (auth()->user()->hasRole('worker')) {
            $query->whereHas('order_status', function ($q) {
                $q->where('worker_id', auth()->user()->id);
            })->latest();
        } else {
            $query->where('user_id', auth()->user()->id)->latest();
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order_responses()
    {
        return $this->hasMany(OrderResponse::class, 'order_id', 'uuid');
    }

    public function order_area()
    {
        return $this->hasOne(OrderArea::class, 'order_id', 'uuid');
    }

    public function order_status()
    {
        return $this->hasOne(OrderStatus::class, 'order_id', 'uuid');
    }

    // get accepted response
    public function accepted_response()
    {
        return $this->hasOne(OrderResponse::class, 'order_id', 'uuid')->where('response_type', 'ACCEPTED');
    }

    // get schedule response
    public function schedule_response()
    {
        return $this->hasOne(OrderResponse::class, 'order_id', 'uuid')->where('response_type', 'SCHEDULE');
    }

    // get customer response
    public function accepted_schedule_response()
    {
        return $this->hasOne(OrderArea::class, 'order_id', 'uuid')->where('customer_response', 'ACCEPTED');
    }

    public function hasCustomerResponse()
    {
        return $this->hasOne(OrderArea::class, 'order_id', 'uuid')->where('customer_response', 'ACCEPTED')->orWhere('customer_response', 'REJECTED')->orWhere('customer_response', 'RESUBMIT');
    }

    // user has submitted area
    public function userSubmittedArea()
    {
        return $this->hasOne(OrderArea::class, 'order_id', 'uuid')->where('worker_id', auth()->user()->id);
    }

    public function hasBeenScheduled()
    {
        $r = $this->order_responses->where('user_id', auth()->user()->id)->where('time', '!=' ,null);
        if($r->count()){
            return $r;
        }else{
            return false;
        }
    }

    // get scehdule data
    public function schedule_data()
    {
        $o_status=$this->order_status()->where('order_id', $this->uuid)->first();
        if($o_status){
            $o_res=$this->order_responses()->where('user_id', $o_status->worker_id)->where('time', '!=' ,null)->first();
            if ($o_res) {
                return $o_res;
            } else {
                return false;
            }
            
        }else{
            return false;
        }
    }

    public function accepted_response_user()
    {
        $r = $this->order_responses->where('user_id', auth()->user()->id);
        if($r->count()){
            return $r;
        }else{
            return false;
        }
    }

    //  response has been rejected
    public function rejected_response()
    {
        return $this->hasOne(OrderResponse::class, 'order_id', 'uuid')->where('response_type', 'REJECTED');
    }

    public function scopeOrderByName($query)
    {
        $query->orderBy('city')->orderBy('state')->orderBy('country');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('city', 'like', '%'.$search.'%')
                    ->orWhere('state', 'like', '%'.$search.'%')
                    ->orWhere('lat', 'like', '%'.$search.'%')
                    ->orWhere('lng', 'like', '%'.$search.'%')
                    ->orWhere('country', 'like', '%'.$search.'%')
                    ->orWhere('full_address', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                    });
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
