<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
	protected $table = 'subscriptions';

	protected $fillable = [
	    'subs_id','plan_id','customer_id','start_date','end_date'
	];

    public function user()
	{
	    return $this->belongsTo('App\Models\User', 'customer_id', 'square_customer_id');
	}

	public function plan()
	{
	    return $this->belongsTo('App\Models\Plan', 'plan_id', 'plan_id');
	}
}
