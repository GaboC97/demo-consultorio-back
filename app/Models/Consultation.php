<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory; // Agregamos esto para que funcionen tus Seeders

    protected $fillable = [
        'patient_id',
        'user_id',
        'reason',
        'symptoms',
        'physical_exam',
        'diagnosis',
        'treatment'
    ];

    /**
     * El paciente al que pertenece la consulta.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * El médico (User) que realizó la consulta.
     * Usamos 'user_id' como clave foránea.
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
