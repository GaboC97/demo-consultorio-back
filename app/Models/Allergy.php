<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    use HasFactory;

    // Permitimos que se pueda guardar el nombre desde el controlador
    protected $fillable = ['name'];

    /**
     * Relación con los Pacientes (Muchos a Muchos)
     * Esto permite saber qué pacientes tienen esta alergia.
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'allergy_patient')
                    ->withTimestamps();
    }
}