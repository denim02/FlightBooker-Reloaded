<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class ReservedSeat extends Model
{
    protected $appends = [
        'seat_code',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class, 'flight_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function seatCode()
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['row_no'] . SeatColumnHelper::numberToLetter($attributes['col_no'])
        );
    }
}

class SeatColumnHelper
{
    public static function numberToLetter(int $columnNumber): string
    {
        return chr($columnNumber + 64);
    }

    public static function letterToNumber(string $letter): int
    {
        return ord($letter) - 64;
    }
}
