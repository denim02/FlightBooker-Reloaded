<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected $appends = ['role', 'permissions'];

    public function getRoleAttribute()
    {
        return count($this->roles) > 0 ? $this->roles[0] : null;
    }

    public function getPermissionsAttribute()
    {
        if ($this->is_admin) {
            return Permission::all()->pluck('name');
        }

        return $this->getAllPermissions()->pluck('name');
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }
}
