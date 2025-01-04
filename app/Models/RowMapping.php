<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RowMapping extends Pivot
{
    public $incrementing = true;
    protected $table = 'row_seat_class_mappings';

    protected $casts = [
        'rows' => 'array',
    ];

    public function airplane()
    {
        return $this->belongsTo(Airplane::class);
    }

    public function seatClass()
    {
        return $this->belongsTo(SeatClass::class);
    }
}
