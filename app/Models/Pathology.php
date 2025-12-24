<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pathology extends Model
{
    use HasFactory;

    // Habilitamos la carga masiva para el nombre de la enfermedad
    protected $fillable = ['name'];

    /**
     * Relación con los Pacientes (Muchos a Muchos).
     * Esto permite vincular enfermedades previas a la ficha del paciente.
     */
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'patient_pathology')
                    ->withPivot('status') // Por si quieres guardar si está "Activa" o "Curada"
                    ->withTimestamps();
    }
}