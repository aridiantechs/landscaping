<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    protected $table="rating";

    protected $fillable=[
        'order_id',
        "rating",
        "comment"
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'uuid');
    }
}
