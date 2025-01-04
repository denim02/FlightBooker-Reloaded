<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airplane extends Model
{
    use HasTimestamps;

    protected $fillable = [
        'brand',
        'model',
        'nr_rows',
        'nr_cols'
    ];

    public function totalCapacity(): Attribute
    {
        return Attribute::make(
            get: fn(mixed $value, $attributes) => $attributes['nr_rows'] * $attributes['nr_cols']
        );
    }

    public function rowMappings()
    {
        return $this->belongsToMany(SeatClass::class, 'row_seat_class_mappings')->using(RowMapping::class)->withPivot('rows');
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }
}
