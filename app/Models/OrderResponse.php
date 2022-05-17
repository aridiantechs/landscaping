<?php

namespace App\Models;

use App\Models\User;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderResponse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_responses';
    protected $fillable=[
        'order_id',
        'user_id',
        'response_user',
        'response_type',
        'time',
        'comments',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
