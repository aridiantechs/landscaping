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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order_responses()
    {
        return $this->hasMany(OrderResponse::class);
    }

    public function order_area()
    {
        return $this->hasMany(OrderArea::class);
    }

    public function order_status()
    {
        return $this->hasOne(OrderStatus::class);
    }

}
