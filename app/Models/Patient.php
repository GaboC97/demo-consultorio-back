<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    // Campos que permitimos llenar desde el Front-end
protected $fillable = [
  'first_name','last_name','dni','birth_date','phone','social_security',
  'gender','blood_type'
];


    /**
     * Relación con las Consultas (Historia Clínica)
     */
    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }

    /**
     * Relación con Patologías (Antecedentes)
     */
    public function pathologies()
    {
        return $this->belongsToMany(Pathology::class, 'patient_pathology')
                    ->withTimestamps();
    }

    /**
     * Relación con Alergias (Catálogo)
     */
public function allergies()
{
    return $this->belongsToMany(\App\Models\Allergy::class, 'allergy_patient')
        ->withTimestamps();
}



    public function users()
{
    return $this->belongsToMany(\App\Models\User::class)->withTimestamps();
}

}