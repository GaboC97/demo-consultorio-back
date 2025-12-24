<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pathology extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'patient_pathology')
            ->withPivot('status')
            ->withTimestamps();
    }
}
