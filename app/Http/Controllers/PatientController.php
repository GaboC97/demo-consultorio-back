<?php

namespace App\Http\Controllers;


use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class PatientController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->patients()
            ->with(['allergies', 'pathologies'])
            ->withMax('consultations as ultima_consulta', 'created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('dni', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('modal')) {
            return response()->json($query->limit(10)->get());
        }

        return response()->json($query->paginate(10));
    }

    public function show($id)
    {
        $patient = Patient::with([
            'pathologies',
            'allergies',
            'consultations' => fn($q) => $q->orderBy('created_at', 'desc')
        ])->findOrFail($id);

        return response()->json(['data' => $patient]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'dni'             => 'required|string|max:20',
            'email'           => 'nullable|email|max:255',
            'birth_date'      => 'nullable|date',
            'phone'           => 'nullable|string',
            'social_security' => 'nullable|string|max:255',
            'gender'          => 'nullable|in:M,F,X',
            'blood_type'      => 'nullable|in:O+,O-,A+,A-,B+,B-,AB+,AB-',
            'allergy_ids'     => 'nullable|array',
            'allergy_ids.*'   => 'integer|exists:allergies,id',
            'pathology_ids'   => 'nullable|array',
            'pathology_ids.*' => 'integer|exists:pathologies,id',
        ]);

        $patient = Patient::updateOrCreate(
            ['dni' => $validated['dni']],
            collect($validated)->only([
                'first_name',
                'last_name',
                'email',
                'birth_date',
                'phone',
                'social_security',
                'gender',
                'blood_type',
            ])->toArray()
        );

        $request->user()->patients()->syncWithoutDetaching([$patient->id]);

        if ($request->has('allergy_ids')) {
            $patient->allergies()->sync($validated['allergy_ids'] ?? []);
        }
        if ($request->has('pathology_ids')) {
            $patient->pathologies()->sync($validated['pathology_ids'] ?? []);
        }

        return response()->json([
            'message' => 'Paciente creado/asociado con éxito',
            'patient' => $patient->load(['allergies', 'pathologies']),
        ], 201);
    }


public function update(Request $request, Patient $paciente)
{
    if ($request->has('email')) {
        $email = $request->input('email');
        if (is_string($email)) {
            $email = trim($email);
            $request->merge([
                'email' => $email === '' ? null : strtolower($email),
            ]);
        }
    }

    // ✅ Validación
    $validados = $request->validate([
        'first_name'      => 'required|string|max:100',
        'last_name'       => 'required|string|max:100',
        'dni'             => 'sometimes|string|max:20|unique:patients,dni,' . $paciente->id,
        'phone'           => 'nullable|string',
        'email'           => 'sometimes|nullable|email|max:255',
        'birth_date'      => 'nullable|date',
        'social_security' => 'nullable|string|max:255',
        'gender'          => 'nullable|in:M,F,X',
        'blood_type'      => 'nullable|in:O+,O-,A+,A-,B+,B-,AB+,AB-',
        'allergy_ids'     => 'nullable|array',
        'allergy_ids.*'   => 'integer|exists:allergies,id',
        'pathology_ids'   => 'nullable|array',
        'pathology_ids.*' => 'integer|exists:pathologies,id',
    ]);
    $dataUpdate = collect($validados)->except(['allergy_ids', 'pathology_ids'])->toArray();
    if (isset($dataUpdate['first_name'])) $dataUpdate['first_name'] = mb_strtoupper($dataUpdate['first_name']);
    if (isset($dataUpdate['last_name']))  $dataUpdate['last_name']  = mb_strtoupper($dataUpdate['last_name']);

    $paciente->update($dataUpdate);

    if ($request->has('allergy_ids')) {
        $paciente->allergies()->sync($validados['allergy_ids'] ?? []);
    }

    if ($request->has('pathology_ids')) {
        $paciente->pathologies()->sync($validados['pathology_ids'] ?? []);
    }

    $paciente->refresh()->load(['allergies', 'pathologies']);

    return response()->json([
        'mensaje' => 'Ficha clínica actualizada correctamente',
        'data'    => $paciente,
    ]);
}



    public function updateBackground(Request $request, Patient $paciente)
    {
        $validated = $request->validate([
            'pathologies'   => 'nullable|array',
            'pathologies.*' => 'integer|exists:pathologies,id',
            'allergies'     => 'nullable|array',
            'allergies.*'   => 'integer|exists:allergies,id',
        ]);

        if ($request->has('pathologies')) {
            $paciente->pathologies()->sync($validated['pathologies'] ?? []);
        }

        if ($request->has('allergies')) {
            $paciente->allergies()->sync($validated['allergies'] ?? []);
        }

        return response()->json(['message' => 'Antecedentes actualizados correctamente']);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return response()->json(['mensaje' => 'Paciente eliminado del sistema']);
    }
}
