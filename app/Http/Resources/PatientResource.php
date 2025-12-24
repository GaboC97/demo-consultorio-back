<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->first_name,
            'apellido' => $this->last_name,
            'nombre_completo' => "{$this->last_name}, {$this->first_name}",
            'dni' => $this->dni,
            'fecha_nacimiento' => $this->birth_date,
            'edad' => \Carbon\Carbon::parse($this->birth_date)->age, // Edad calculada
            'genero' => $this->gender,
            'tipo_sangre' => $this->blood_type,
            'telefono' => $this->phone,
            'email' => $this->email,
            'alergias_notas' => $this->allergies, // El campo de texto libre

            // Aquí traemos los nombres de las tablas pivote que configuramos
            'lista_alergias' => $this->allergies_list ? $this->allergies_list->pluck('name') : [],
            'antecedentes' => $this->pathologies ? $this->pathologies->pluck('name') : [],

            // Si queremos ver sus consultas previas (cuando entramos al detalle)
            'consultas' => ConsultationResource::collection($this->whenLoaded('consultations')),
        ];
    }
}
