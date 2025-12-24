<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Patient;
use App\Http\Resources\ConsultationResource;
use App\Http\Requests\StoreConsultationRequest;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index()
    {
        return ConsultationResource::collection(Consultation::with(['patient', 'doctor'])->latest()->paginate(10));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'patient_id' => 'required|exists:patients,id',
        'reason'     => 'required|string|max:255',
        'diagnosis'  => 'required|string',
        'treatment'  => 'required|string',
    ]);

    $consulta = Consultation::create([
        'patient_id' => $validated['patient_id'],
        'user_id'    => auth()->id(), 
        'reason'     => $validated['reason'],
        'diagnosis'  => $validated['diagnosis'],
        'treatment'  => $validated['treatment'],
    ]);

    return response()->json(['mensaje' => 'Consulta registrada con éxito'], 201);
}

    public function history(Patient $patient)
    {
        $consultas = $patient->consultations()->with('doctor')->latest()->get();
        return ConsultationResource::collection($consultas);
    }

    public function update(Request $request, Consultation $consultation)
    {
        $validados = $request->validate([
            'reason'    => 'required|string',
            'diagnosis' => 'required|string',
            'treatment' => 'required|string',
            'symptoms'  => 'nullable|string',
            'physical_exam' => 'nullable|string',
        ]);

        $consultation->update($validados);

        return response()->json([
            'mensaje' => 'Consulta actualizada',
            'data' => new ConsultationResource($consultation)
        ]);
    }

    public function destroy(Consultation $consultation)
    {
        $consultation->delete();
        return response()->json(['mensaje' => 'Consulta eliminada']);
    }
}
