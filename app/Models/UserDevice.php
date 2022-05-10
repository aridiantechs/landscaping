<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
	protected $table = 'user_devices';

	protected $fillable = [
	    'device_id', 'user_id'
	];

    public function user()
	{
	    return $this->belongsTo('App\User', 'user_id');
	}
}
