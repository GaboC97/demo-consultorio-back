<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\TurnoAdjunto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TurnoController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        return Turno::with([
            'paciente:id,first_name,last_name,dni',
            'adjuntos',
            'medicoEmisor' => function ($query) {
                $query->select('id', 'name as nombre', 'mn_number as mn', 'mp_number as mp');
            },
            'medicoReceptor:id,name as nombre'
        ])
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhere('medico_receptor_id', $userId);
            })
            ->orderByRaw('fecha IS NULL DESC')
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();
    }

    public function store(Request $request)
    {
        $esDerivacion = $request->input('es_derivacion') == '1' || $request->input('es_derivacion') === 'true';
        $userId = Auth::id();

        $validated = $request->validate([
            'paciente_id'        => 'required|exists:patients,id',
            'fecha'              => $esDerivacion ? 'nullable|date' : 'required|date',
            'hora'               => $esDerivacion ? 'nullable' : 'required',
            'motivo'             => 'nullable|string',
            'medico_receptor_id' => $esDerivacion ? 'required|exists:users,id' : 'nullable',
            'prioridad'          => $esDerivacion ? 'required|in:baja,media,alta,urgente' : 'nullable',
            'archivos.*'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($esDerivacion && $validated['medico_receptor_id'] == $userId) {
            return response()->json(['error' => 'No puedes realizar una derivación a ti mismo.'], 422);
        }

        try {
            return DB::transaction(function () use ($validated, $esDerivacion, $request, $userId) {

                $turno = Turno::create([
                    'paciente_id'        => $validated['paciente_id'],
                    'user_id'            => $userId,
                    'medico_receptor_id' => $esDerivacion ? $validated['medico_receptor_id'] : null,
                    'fecha'              => $validated['fecha'] ?? null,
                    'hora'               => $validated['hora'] ?? null,
                    'motivo'             => $validated['motivo'] ?? null,
                    'es_derivacion'      => $esDerivacion ? 1 : 0,
                    'prioridad'          => $esDerivacion ? ($validated['prioridad'] ?? 'baja') : null,
                    'estado'             => 'pendiente'
                ]);

                if ($request->hasFile('archivos')) {
                    foreach ($request->file('archivos') as $archivo) {
                        $path = $archivo->store('adjuntos/turnos', 'public');
                        TurnoAdjunto::create([
                            'turno_id'        => $turno->id,
                            'nombre_original' => $archivo->getClientOriginalName(),
                            'ruta'            => $path,
                            'mime_type'       => $archivo->getClientMimeType(),
                            'size'            => $archivo->getSize(),
                        ]);
                    }
                }

                return response()->json([
                    'message' => $esDerivacion ? 'Interconsulta enviada' : 'Turno registrado',
                    'turno'   => $turno->load(['paciente', 'adjuntos', 'medicoEmisor', 'medicoReceptor'])
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ UPDATE: Editar turno / derivación
     * Reglas:
     * - Solo el emisor (user_id) puede editar
     * - No permitir derivar a uno mismo
     * - Derivación confirmada no editable (podés cambiar esta regla si querés)
     * - Adjuntos: permite agregar nuevos (no borra existentes aquí)
     */
    public function update(Request $request, Turno $turno)
    {
        $userId = Auth::id();
        
        if ((int)$turno->user_id !== (int)$userId) {
            return response()->json(['error' => 'No tienes permiso para editar este turno.'], 403);
        }

        $esDerivacion = (int)$turno->es_derivacion === 1;

        if ($esDerivacion && $turno->estado === 'confirmado') {
            return response()->json(['error' => 'No puedes editar una derivación ya confirmada.'], 422);
        }

        $validated = $request->validate([
            'paciente_id'        => 'required|exists:patients,id',
            'motivo'             => 'nullable|string',
            'fecha'              => $esDerivacion ? 'nullable|date' : 'required|date',
            'hora'               => $esDerivacion ? 'nullable' : 'required',
            'medico_receptor_id' => $esDerivacion ? 'required|exists:users,id' : 'nullable',
            'prioridad'          => $esDerivacion ? 'required|in:baja,media,alta,urgente' : 'nullable',
            'archivos.*'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($esDerivacion && (int)$validated['medico_receptor_id'] === (int)$userId) {
            return response()->json(['error' => 'No puedes realizar una derivación a ti mismo.'], 422);
        }

        try {
            return DB::transaction(function () use ($turno, $validated, $request, $esDerivacion) {

                $turno->paciente_id = $validated['paciente_id'];
                $turno->motivo      = $validated['motivo'] ?? null;

                if ($esDerivacion) {
                    $turno->medico_receptor_id = $validated['medico_receptor_id'];
                    $turno->prioridad          = $validated['prioridad'] ?? 'baja';

                    $turno->fecha = $turno->fecha;
                    $turno->hora  = $turno->hora;
                } else {
                    $turno->fecha = $validated['fecha'];
                    $turno->hora  = $validated['hora'];
                }

                $turno->save();

                if ($request->hasFile('archivos')) {
                    foreach ($request->file('archivos') as $archivo) {
                        $path = $archivo->store('adjuntos/turnos', 'public');
                        TurnoAdjunto::create([
                            'turno_id'        => $turno->id,
                            'nombre_original' => $archivo->getClientOriginalName(),
                            'ruta'            => $path,
                            'mime_type'       => $archivo->getClientMimeType(),
                            'size'            => $archivo->getSize(),
                        ]);
                    }
                }

                return response()->json([
                    'message' => 'Turno actualizado',
                    'turno'   => $turno->load(['paciente', 'adjuntos', 'medicoEmisor', 'medicoReceptor'])
                ]);
            });

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $turno = Turno::where('user_id', Auth::id())->with('adjuntos')->findOrFail($id);

        foreach ($turno->adjuntos as $adjunto) {
            Storage::disk('public')->delete($adjunto->ruta);
        }

        $turno->delete();
        return response()->json(['message' => 'Turno cancelado']);
    }

    public function aceptarDerivacion(Request $request, Turno $turno)
    {
        if ((int)$turno->medico_receptor_id !== (int)Auth::id()) {
            return response()->json(['error' => 'No tienes permiso para aceptar esta derivación'], 403);
        }

        $request->validate([
            'fecha' => 'required|date',
            'hora'  => 'required',
        ]);

        $turno->update([
            'fecha'  => $request->fecha,
            'hora'   => $request->hora,
            'estado' => 'confirmado'
        ]);

        return response()->json(['message' => 'Derivación agendada correctamente']);
    }


    public function destroyAdjunto(TurnoAdjunto $adjunto)
    {
        $turno = $adjunto->turno;
        if ((int)$turno->user_id !== (int)Auth::id()) {
            return response()->json(['error' => 'No tienes permiso para borrar este archivo.'], 403);
        }

        Storage::disk('public')->delete($adjunto->ruta);
        $adjunto->delete();

        return response()->json(['message' => 'Adjunto eliminado']);
    }

}
