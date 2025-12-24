<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
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
            'fecha' => $this->created_at->format('d-m-Y H:i'),
            'motivo' => $this->reason,
            'sintomas' => $this->symptoms,
            'examen_fisico' => $this->physical_exam,
            'diagnostico' => $this->diagnosis,
            'tratamiento' => $this->treatment,
            'medico' => [
                'nombre' => $this->doctor->name,
                'especialidad' => $this->doctor->specialty,
                'mn' => $this->doctor->mn_number,
                'mp' => $this->doctor->mp_number,
            ]
        ];
    }
}
