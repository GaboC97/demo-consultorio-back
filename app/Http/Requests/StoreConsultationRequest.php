<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'reason'     => 'required|string|min:5',
            'diagnosis'  => 'required|string|min:3',
            'treatment'  => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required'    => 'Debe ingresar el motivo de la consulta.',
            'diagnosis.required' => 'El diagnóstico es obligatorio para el historial.',
            'treatment.required' => 'Debe indicar el tratamiento o pasos a seguir.',
        ];
    }
}
