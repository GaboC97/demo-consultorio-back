<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Cambiar a true para permitir la operación
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'dni'        => 'required|string|unique:patients,dni|max:20',
            'birth_date' => 'required|date|before:today',
            'gender'     => 'nullable|in:M,F,Otro',
            'blood_type' => 'nullable|string',
            'email'      => 'nullable|email',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'last_name.required'  => 'El apellido es obligatorio.',
            'dni.required'        => 'El DNI es obligatorio para la ficha médica.',
            'dni.unique'          => 'Ya existe un paciente registrado con este DNI.',
            'birth_date.before'   => 'La fecha de nacimiento no puede ser futura.',
        ];
    }
}
