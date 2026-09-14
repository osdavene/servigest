<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OrdenTrabajo;
use App\Services\QrCodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketPosController extends Controller
{
    /**
     * Muestra el ticket térmico optimizado para impresoras POS de 58mm y 80mm.
     */
    public function ticket(Request $request, OrdenTrabajo $orden): View
    {
        $this->authorize('view', $orden);

        $orden->load(['taller', 'cliente', 'equipo.categoria', 'tecnico']);
        $taller = $orden->taller;

        $anchoPapel = $request->input('ancho', '80'); // 80mm o 58mm
        $urlSeguimiento = route('ordenes.publico', $orden->token_publico_pdf);
        $qrCodeUrl = QrCodeGeneratorService::generarUrlPng($urlSeguimiento, 160);

        return view('ordenes.ticket_pos', compact('orden', 'taller', 'anchoPapel', 'urlSeguimiento', 'qrCodeUrl'));
    }

    /**
     * Muestra la etiqueta adhesiva compacta con QR para pegar en el equipo físico.
     */
    public function etiqueta(OrdenTrabajo $orden): View
    {
        $this->authorize('view', $orden);

        $orden->load(['taller', 'cliente', 'equipo']);
        $taller = $orden->taller;

        $urlSeguimiento = route('ordenes.publico', $orden->token_publico_pdf);
        $qrCodeUrl = QrCodeGeneratorService::generarUrlPng($urlSeguimiento, 180);

        return view('ordenes.etiqueta_qr', compact('orden', 'taller', 'urlSeguimiento', 'qrCodeUrl'));
    }
}
