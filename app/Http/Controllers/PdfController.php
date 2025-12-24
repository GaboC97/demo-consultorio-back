<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Consultation;

class PdfController extends Controller
{


    public function imprimirConsulta($id)
    {
        $consulta = Consultation::with(['patient', 'user'])->findOrFail($id);

        // Datos del médico logueado (vos)
        $medico = $consulta->user;

        $data = [
            'consulta' => $consulta,
            'paciente' => $consulta->patient,
            'medico'   => $medico,
            'fecha'    => $consulta->created_at->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('pdfs.consulta', $data);

        // Esto descarga el PDF con un nombre descriptivo
        return $pdf->download("consulta_{$consulta->patient->last_name}.pdf");
    }
}
