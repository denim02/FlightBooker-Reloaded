<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    use HasTimestamps;

    protected $fillable = [
        'name',
        'phone_number',
        'email',
        'country'
    ];

    public function operators()
    {
        return $this->hasMany(User::class, 'id', 'operator_id');
    }

    public function routes()
    {
        return $this->hasMany(Route::class);
    }
}
