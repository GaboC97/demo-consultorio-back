<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

protected $fillable = [
  'first_name',
  'last_name',
  'dni',
  'birth_date',
  'gender',
  'blood_type',
  'phone',
  'social_security',
  'email',
];



    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }


    public function pathologies()
    {
        return $this->belongsToMany(Pathology::class, 'patient_pathology')
            ->withTimestamps();
    }


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
