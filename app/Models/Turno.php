<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turno extends Model
{
    use HasFactory;

    protected $fillable = [
        'paciente_id',
        'user_id',
        'medico_receptor_id',
        'fecha',
        'hora',
        'motivo',
        'estado',
        'prioridad',
        'es_derivacion',
    ];

    protected $casts = [
        'es_derivacion' => 'boolean',
    ];

    public function paciente()
    {
        return $this->belongsTo(Patient::class, 'paciente_id');
    }

    public function medico()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function adjuntos()
    {
        return $this->hasMany(TurnoAdjunto::class);
    }

    public function medicoEmisor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function medicoReceptor()
    {
        return $this->belongsTo(User::class, 'medico_receptor_id');
    }
}
