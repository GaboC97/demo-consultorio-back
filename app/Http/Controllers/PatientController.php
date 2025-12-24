<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Http\Resources\PatientResource;
use Illuminate\Http\Request;
use App\Http\Requests\StorePatientRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
public function index(Request $request)
{
    $query = $request->user()->patients()
        ->with(['allergies', 'pathologies'])
        ->withMax('consultations as ultima_consulta', 'created_at');

    // Aplicar filtros de búsqueda si existen
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('last_name', 'LIKE', "%{$search}%")
              ->orWhere('first_name', 'LIKE', "%{$search}%")
              ->orWhere('dni', 'LIKE', "%{$search}%");
        });
    }

    // LÓGICA HÍBRIDA:
    // Si la petición viene del modal (donde no queremos paginación), 
    // mandamos un parámetro extra 'nopaginate' o simplemente limitamos si hay búsqueda.
    if ($request->has('modal')) {
        return response()->json($query->limit(10)->get());
    }

    // De lo contrario, sigue funcionando igual para tu tabla de pacientes
    return response()->json($query->paginate(10));
}

public function show($id)
{
    $patient = Patient::with([
        'pathologies',
        'allergies',
        'consultations' => fn ($q) => $q->orderBy('created_at', 'desc')
    ])->findOrFail($id);

    return response()->json(['data' => $patient]);
}


public function store(Request $request)
{
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'dni' => 'required|string|max:20',
        'birth_date' => 'nullable|date',
        'phone' => 'nullable|string',
        'social_security' => 'nullable|string|max:255',

        // los agregamos ahora:
        'gender' => 'nullable|in:M,F,X',
        'blood_type' => 'nullable|in:O+,O-,A+,A-,B+,B-,AB+,AB-',

        // para alergias / patologías:
        'allergy_ids' => 'array',
        'allergy_ids.*' => 'integer|exists:allergies,id',
        'pathology_ids' => 'array',
        'pathology_ids.*' => 'integer|exists:pathologies,id',
    ]);

    // si el dni existe, no duplico
    $patient = \App\Models\Patient::firstOrCreate(
        ['dni' => $validated['dni']],
        collect($validated)->only([
            'first_name','last_name','birth_date','phone','social_security','gender','blood_type'
        ])->toArray()
    );

    // asocio al médico logueado (tabla patient_user)
    $request->user()->patients()->syncWithoutDetaching([$patient->id]);

    // pivotes (si vienen)
    if (!empty($validated['allergy_ids'])) {
        $patient->allergies()->sync($validated['allergy_ids']);
    }
    if (!empty($validated['pathology_ids'])) {
        $patient->pathologies()->sync($validated['pathology_ids']);
    }

    return response()->json([
        'message' => 'Paciente creado/asociado con éxito',
        'patient' => $patient->load(['allergies','pathologies']),
    ], 201);
}


public function update(Request $request, Patient $paciente)
{
    $validados = $request->validate([
        'first_name'      => 'required|string|max:100',
        'last_name'       => 'required|string|max:100',
        'dni'             => 'required|string|max:20|unique:patients,dni,' . $paciente->id,
        'phone'           => 'nullable|string',
        'email'           => 'nullable|email',
        'social_security' => 'nullable|string|max:255',
        'gender'          => 'nullable|in:M,F,X',
        'blood_type'      => 'nullable|in:O+,O-,A+,A-,B+,B-,AB+,AB-',
        // Validamos los arrays de IDs para las relaciones
        'allergy_ids'     => 'nullable|array',
        'allergy_ids.*'   => 'integer|exists:allergies,id',
        'pathology_ids'   => 'nullable|array',
        'pathology_ids.*' => 'integer|exists:pathologies,id',
    ]);

    // Actualizamos datos básicos
    $paciente->update(collect($validados)->except(['allergy_ids', 'pathology_ids'])->toArray());

    // Sincronizamos Alergias y Patologías (Tablas pivote)
    if ($request->has('allergy_ids')) {
        $paciente->allergies()->sync($validados['allergy_ids']);
    }
    if ($request->has('pathology_ids')) {
        $paciente->pathologies()->sync($validados['pathology_ids']);
    }

    return response()->json([
        'mensaje' => 'Ficha clínica actualizada correctamente',
        'data' => $paciente->load(['allergies', 'pathologies'])
    ]);
}


public function updateBackground(Request $request, Patient $paciente)
{
    $validated = $request->validate([
        'pathologies' => 'array',
        'pathologies.*' => 'integer|exists:pathologies,id',
        'allergies' => 'array',
        'allergies.*' => 'integer|exists:allergies,id',
    ]);

    if ($request->has('pathologies')) {
        $paciente->pathologies()->sync($validated['pathologies']);
    }

    if ($request->has('allergies')) {
        $paciente->allergies()->sync($validated['allergies']);
    }

    return response()->json(['message' => 'Antecedentes actualizados correctamente']);
}


    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(['mensaje' => 'Paciente eliminado del sistema']);
    }
}
