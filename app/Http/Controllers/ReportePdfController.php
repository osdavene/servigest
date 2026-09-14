<?php

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReportePdfController extends Controller
{
    /**
     * Descarga del PDF para usuarios autenticados dentro del sistema.
     */
    public function descargarPdf(OrdenTrabajo $orden)
    {
        $orden->load(['cliente', 'equipo.categoria', 'tecnico', 'evidencias', 'taller']);

        $pdf = Pdf::loadView('ordenes.pdf_plantilla', compact('orden'))
            ->setPaper('letter', 'portrait');

        return $pdf->download("informe_servicio_{$orden->codigo_orden}.pdf");
    }

    /**
     * Vista y descarga pública para el cliente final mediante enlace seguro de WhatsApp.
     */
    public function verPdfPublico(string $token)
    {
        // Se busca la orden sin el TallerScope de sesión activa para permitir acceso público al cliente
        $orden = OrdenTrabajo::withoutGlobalScopes()
            ->with(['cliente', 'equipo.categoria', 'tecnico', 'evidencias', 'taller'])
            ->where('token_publico_pdf', $token)
            ->firstOrFail();

        return view('ordenes.publico_pdf', compact('orden'));
    }

    /**
     * Descarga directa del PDF para el cliente final mediante token público.
     */
    public function descargarPdfPublico(string $token)
    {
        $orden = OrdenTrabajo::withoutGlobalScopes()
            ->with(['cliente', 'equipo.categoria', 'tecnico', 'evidencias', 'taller'])
            ->where('token_publico_pdf', $token)
            ->firstOrFail();

        $pdf = Pdf::loadView('ordenes.pdf_plantilla', compact('orden'))
            ->setPaper('letter', 'portrait');

        return $pdf->download("informe_servicio_{$orden->codigo_orden}.pdf");
    }
}
