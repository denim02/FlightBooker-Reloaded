<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class SeatClassPricing extends Pivot
{
    protected $table = 'seat_class_pricings';

    protected $fillable = [
        'seat_class_id',
        'route_id',
        'price_per_seat'
    ];

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function seatClass()
    {
        return $this->belongsTo(SeatClass::class);
    }
}
