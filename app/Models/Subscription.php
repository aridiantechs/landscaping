<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
	protected $table = 'subscriptions';

	protected $fillable = [
	    'subs_id','plan_id','customer_id','start_date','end_date','trial_end_at'
	];

    public function user()
	{
	    return $this->belongsTo('App\Models\User', 'customer_id', 'square_customer_id');
	}

	public function plan()
	{
	    return $this->belongsTo('App\Models\Plan', 'plan_id', 'plan_id');
	}

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('city', 'like', '%'.$search.'%')
                    ->orWhere('state', 'like', '%'.$search.'%')
                    ->orWhere('lat', 'like', '%'.$search.'%')
                    ->orWhere('lng', 'like', '%'.$search.'%')
                    ->orWhere('country', 'like', '%'.$search.'%')
                    ->orWhere('full_address', 'like', '%'.$search.'%')
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->where('name', 'like', '%'.$search.'%');
                    });
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }
}
