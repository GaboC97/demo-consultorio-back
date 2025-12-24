<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        $user = $request->user();
        $hoy = now()->format('Y-m-d');
        $totalPacientes = $user->patients()->count();

        $nuevos7d = $user->patients()
            ->wherePivot('created_at', '>=', now()->subDays(7))
            ->count();

        $consultasMes = \App\Models\Consultation::where('user_id', $user->id)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $pendientesHoy = \App\Models\Turno::where('user_id', $user->id)
            ->where('fecha', $hoy)
            ->where('estado', 'pendiente')
            ->count();

        $recientes = \App\Models\Consultation::where('user_id', $user->id)
            ->latest('updated_at')
            ->limit(5)
            ->with('patient:id,first_name,last_name')
            ->get()
            ->map(function ($c) {
                $p = $c->patient;
                return [
                    'id' => $c->id,
                    'paciente' => strtoupper($p->last_name) . ', ' . $p->first_name,
                    'iniciales' => strtoupper(substr($p->last_name, 0, 1) . substr($p->first_name, 0, 1)),
                    'accion' => 'Consulta registrada',
                    'tiempo' => $c->updated_at->diffForHumans(),
                ];
            });

        $turnosHoy = \App\Models\Turno::where('user_id', $user->id)
            ->where('fecha', $hoy)
            ->where('estado', 'pendiente')
            ->with('paciente:id,first_name,last_name')
            ->orderBy('hora', 'asc')
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'hora' => \Carbon\Carbon::parse($t->hora)->format('H:i'),
                    'paciente' => strtoupper($t->paciente->last_name) . ', ' . $t->paciente->first_name,
                    'motivo' => $t->motivo,
                ];
            });

        $proximos7Dias = now()->addDays(7)->format('Y-m-d');

        $turnosProximos = \App\Models\Turno::where('user_id', $user->id)
            ->whereBetween('fecha', [$hoy, $proximos7Dias])
            ->where('estado', 'pendiente')
            ->with('paciente:id,first_name,last_name')
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->limit(10)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'fecha_formateada' => \Carbon\Carbon::parse($t->fecha)->format('d/m'),
                    'hora' => \Carbon\Carbon::parse($t->hora)->format('H:i'),
                    'paciente' => strtoupper($t->paciente->last_name) . ', ' . $t->paciente->first_name,
                    'motivo' => $t->motivo,
                ];
            });

        return response()->json([
            'stats' => [
                'total_pacientes' => $totalPacientes,
                'consultas_mes' => $consultasMes,
                'nuevos_pacientes' => $nuevos7d,
                'pendientes' => $pendientesHoy,
            ],
            'recientes' => $recientes,
            'turnos_hoy' => $turnosHoy,
            'turnos_proximos' => $turnosProximos
        ]);
    }
}
