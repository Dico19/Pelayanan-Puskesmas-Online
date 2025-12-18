<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'poli_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role_id' => 'integer',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // helper (opsional tapi enak dipakai)
    public function isAdmin(): bool
    {
        return $this->role?->role === 'admin';
    }

    public function isDoctor(): bool
    {
        return str_starts_with((string) $this->role?->role, 'dokter_');
    }
}
