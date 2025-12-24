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
        'mn_number',
        'mp_number',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

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
