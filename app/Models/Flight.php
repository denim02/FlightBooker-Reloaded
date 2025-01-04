<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasTimestamps;

    protected $fillable = [
        'departure_time',
        'arrival_time',
        'delay',
        'departure_airport_code',
        'arrival_airport_code',
        'route_id',
        'airplane_id'
    ];

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_code');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_code');
    }

    public function airplane()
    {
        return $this->belongsTo(Airplane::class, 'airplane_id');
    }

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    public function reservedSeats()
    {
        return $this->hasMany(ReservedSeat::class);
    }
}
