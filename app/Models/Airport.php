<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasTimestamps;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'code';

    protected $fillable = [
        'code',
        'name',
        'city',
        'country'
    ];

    public function departingFlights()
    {
        return $this->hasMany(Flight::class, 'departure_airport_code');
    }

    public function arrivingFlights()
    {
        return $this->hasMany(Flight::class, 'arrival_airport_code');
    }

    public function departingRoutes()
    {
        return $this->hasMany(Route::class, 'departure_airport_code');
    }

    public function arrivingRoutes()
    {
        return $this->hasMany(Route::class, 'arrival_airport_code');
    }
}
