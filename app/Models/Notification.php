<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    public function order()
    {
        return $this->belongsTo(Order::class,'req_id');
    }

    // get order response
    public function order_response()
    {
        // fetch order response from object key
        $object=json_decode($this->object);
        if (isset($object->order_r)) {
            return $object->order_r;
        }else{
            return null;
        }

    }
}
