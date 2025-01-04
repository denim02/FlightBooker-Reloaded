<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

enum SeatClassOptions: string
{
    case Economy = 'Economy';
    case PremiumEconomy = 'Premium Economy';
    case BusinessClass = 'Business Class';
    case FirstClass = 'First Class';
}

class SeatClass extends Model
{
    public $timestamps = false;
    protected $casts = [
        'name' => SeatClassOptions::class
    ];

    public function pricings()
    {
        return $this->belongsToMany(Route::class, 'seat_class_pricings')->using(SeatClassPricing::class)->withPivot(['price_per_seat']);
    }

    public function rowMappings()
    {
        return $this->belongsToMany(Airplane::class)->using(RowMapping::class)->withPivot('rows');
    }
}
