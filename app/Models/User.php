<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'specialty',
        'mn_number', // Matrícula Nacional
        'mp_number', // Matrícula Provincial
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Esto es clave en Laravel 10/11
    ];

    // El médico solo se relaciona con las consultas que él mismo redactó
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    public function patients()
{
    return $this->belongsToMany(\App\Models\Patient::class)->withTimestamps();
}

public function conversations(): BelongsToMany
{
    return $this->belongsToMany(
        \App\Models\Conversation::class,
        'conversation_user'
    )
    ->withPivot(['last_read_at', 'role'])
    ->withTimestamps();
}
}
