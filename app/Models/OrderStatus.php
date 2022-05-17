<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Order;

class OrderStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'order_status';

    protected static function boot()
    {
        parent::boot();

        self::addGlobalScope( function ($builder) {
            $builder=$builder->has('order');
            if (auth()->user()->hasRole('worker')) {
                $builder->where('worker_id', auth()->user()->id);
            } else {
                $builder->whereHas('order', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                });
            }
            
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'uuid');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

}
