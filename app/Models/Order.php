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

    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope( function ($builder) {
            if (auth()->user()->hasRole('worker')) {
                $builder->whereHas('order_status', function ($query) {
                    $query->where('worker_id', auth()->user()->id);
                });
            } else {
                $builder->where('user_id', auth()->user()->id);
            }
            
        });
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
}
