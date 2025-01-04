<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

enum RouteFrequency: string
{
    case DAILY = 'DAILY';
    case WEEKLY = 'WEEKLY';
    case MONTHLY = 'MONTHLY';
    case YEARLY = 'YEARLY';
}

class Route extends Model
{
    protected $fillable = [
        'departure_time',
        'arrival_time',
        'is_repeating',
        'frequency',
        'route_group_id',
        'departure_airport_code',
        'arrival_airport_code'
    ];

    protected $with = [
        'flights'
    ];

    protected $casts = [
        'frequency' => RouteFrequency::class
    ];

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_code');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_code');
    }

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    public function pricings()
    {
        return $this->belongsToMany(SeatClass::class, 'seat_class_pricings')->using(SeatClassPricing::class)->withPivot(['price_per_seat']);
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }
}
