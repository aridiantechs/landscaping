<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderResponse extends Model
{
    use HasFactory;

    protected $table = 'order_responses';

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
