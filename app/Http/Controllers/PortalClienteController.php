<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalClienteController extends Controller
{
    /**
     * Muestra el Dashboard corporativo del cliente B2B.
     */
    public function index(string $token): View
    {
        $cliente = Cliente::withoutGlobalScopes()
            ->where('token_portal', $token)
            ->with(['taller', 'equipos.categoria'])
            ->firstOrFail();

        $taller = $cliente->taller;

        // Órdenes del cliente
        $ordenes = OrdenTrabajo::withoutGlobalScopes()
            ->where('cliente_id', $cliente->id)
            ->with(['equipo.categoria', 'tecnico', 'evidencias'])
            ->latest('id')
            ->get();

        $equipos = $cliente->equipos;

        // Métricas
        $totalEquipos = $equipos->count();
        $ordenesActivas = $ordenes->whereIn('estado', ['pendiente', 'en_proceso'])->count();
        $ordenesFinalizadas = $ordenes->whereIn('estado', ['finalizado', 'entregado'])->count();
        $mantenimientosPendientes = $equipos->filter(function ($eq) {
            return $eq->fecha_proximo_mantenimiento && \Carbon\Carbon::parse($eq->fecha_proximo_mantenimiento)->isPast();
        })->count();

        return view('portal_cliente.index', compact(
            'cliente',
            'taller',
            'ordenes',
            'equipos',
            'totalEquipos',
            'ordenesActivas',
            'ordenesFinalizadas',
            'mantenimientosPendientes'
        ));
    }

    /**
     * Procesa la solicitud express de mantenimiento o reparación enviada por el cliente.
     */
    public function solicitarServicio(Request $request, string $token): RedirectResponse
    {
        $cliente = Cliente::withoutGlobalScopes()
            ->where('token_portal', $token)
            ->with('taller')
            ->firstOrFail();

        $taller = $cliente->taller;

        $validados = $request->validate([
            'equipo_id' => ['required', 'integer'],
            'tipo_ubicacion' => ['required', 'in:servicio_en_domicilio,ingresado_al_taller'],
            'problema_reportado' => ['required', 'string', 'max:2000'],
        ], [
            'equipo_id.required' => 'Debe seleccionar el equipo a revisar.',
            'problema_reportado.required' => 'Por favor describa el fallo o solicitud de mantenimiento.',
        ]);

        $equipo = Equipo::withoutGlobalScopes()
            ->where('id', $validados['equipo_id'])
            ->where('cliente_id', $cliente->id)
            ->firstOrFail();

        // Generar código de orden correlativo
        $ultimoId = OrdenTrabajo::withoutGlobalScopes()
            ->where('taller_id', $taller->id)
            ->max('id') ?? 0;

        $prefijo = $taller->prefijo_orden ?: 'OT';
        $codigoOrden = sprintf('%s-%05d', $prefijo, $ultimoId + 1);

        $orden = OrdenTrabajo::withoutGlobalScopes()->create([
            'taller_id' => $taller->id,
            'cliente_id' => $cliente->id,
            'equipo_id' => $equipo->id,
            'codigo_orden' => $codigoOrden,
            'tipo_ubicacion' => $validados['tipo_ubicacion'],
            'estado' => 'pendiente',
            'problema_reportado' => $validados['problema_reportado'],
            'costo_mano_obra' => 0,
            'costo_repuestos' => 0,
            'costo_total' => 0,
            'fecha_ingreso' => now(),
            'token_publico_pdf' => (string) Str::uuid(),
        ]);

        // Disparar notificación si está habilitada
        WhatsAppNotificationService::enviarNotificacionAutomatica($orden, 'creada');

        return redirect()->route('portal.cliente', $cliente->token_portal)
            ->with('exito', "¡Solicitud #{$orden->codigo_orden} enviada exitosamente! Nuestro equipo técnico se pondrá en contacto pronto.");
    }
}
