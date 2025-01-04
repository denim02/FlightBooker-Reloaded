<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasTimestamps;

    protected $fillable = [
        'reservation_date',
        ''
    ];

    protected $with = [
        'reservedSeats',
        'route'
    ];

    protected $appends = [
        'total_fee'
    ];

    public function client()
    {
        return $this->belongsTo(User::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function reservedSeats()
    {
        return $this->hasMany(ReservedSeat::class);
    }

    public function totalFee()
    {
        return Attribute::make(
            get: fn(mixed $value, array $attributes) => array_reduce($this->reservedSeats() ?? [], function ($carry, $item) {
                return $carry + $item->price;
            }, 0),
        );
    }
}
