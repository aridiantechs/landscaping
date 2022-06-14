<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SquareCustomerCard extends Model
{
	protected $table = 'square_customer_card';

	protected $fillable = [
	    'card_id', 'user_id'
	];

    public function user()
	{
	    return $this->belongsTo('App\User', 'user_id');
	}
}
