<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GeoLocation extends Model
{
    use HasFactory;

    protected $table = 'geo_locations';
    protected $fillable=[
        'user_id',
        'city',
        'state',
        'country',
        'lat',
        'lng',
        'full_address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
