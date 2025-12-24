<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoAdjunto extends Model
{
    use HasFactory;

    protected $fillable = ['turno_id', 'nombre_original', 'ruta', 'mime_type', 'size'];

    public function turno()
    {
        return $this->belongsTo(Turno::class);
    }
}
