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
            });
        } else {
            $query->where('user_id', auth()->user()->id);
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

    // get customer response
    public function accepted_schedule_response()
    {
        return $this->hasOne(OrderArea::class, 'order_id', 'uuid')->where('customer_response', 'ACCEPTED');
    }

    public function hasCustomerResponse()
    {
        return $this->hasOne(OrderArea::class, 'order_id', 'uuid')->where('customer_response', 'ACCEPTED')->orWhere('customer_response', 'REJECTED')->orWhere('customer_response', 'RESUBMIT');
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

    public function accepted_response_user()
    {
        $r = $this->order_responses->where('user_id', auth()->user()->id);
        if($r->count()){
            return $r;
        }else{
            return false;
        }
    }
}
