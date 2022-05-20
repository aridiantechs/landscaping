<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderArea extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_area';
    protected $fillable=[
        'order_id',
        'worker_id',
        'width',
        'length',
        'total_amount',
        'customer_response',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'uuid');
    }

    public function worker()
    {
        return $this->belongsTo(User::class);
    }

}
