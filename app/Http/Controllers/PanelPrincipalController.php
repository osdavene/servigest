<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Equipo;
use App\Models\OrdenTrabajo;
use Illuminate\Http\Request;

class PanelPrincipalController extends Controller
{
    public function index(Request $request)
    {
        $usuario = auth()->user();

        // Métricas rápidas
        $totalClientes = Cliente::count();
        $totalEquipos = Equipo::count();
        $ordenesPendientes = OrdenTrabajo::where('estado', 'pendiente')->count();
        $ordenesEnProceso = OrdenTrabajo::where('estado', 'en_proceso')->count();
        $ordenesFinalizadas = OrdenTrabajo::where('estado', 'finalizado')->count();

        // Alertas de mantenimiento preventivo (equipos cuya fecha es hoy o anterior)
        $equiposMantenimientoAlerta = Equipo::with(['cliente', 'categoria'])
            ->whereNotNull('fecha_proximo_mantenimiento')
            ->where('fecha_proximo_mantenimiento', '<=', now()->addDays(7))
            ->orderBy('fecha_proximo_mantenimiento', 'asc')
            ->take(10)
            ->get();

        // Últimas órdenes de trabajo
        $ultimasOrdenes = OrdenTrabajo::with(['cliente', 'equipo', 'tecnico'])
            ->latest('id')
            ->take(8)
            ->get();

        return view('panel.index', compact(
            'totalClientes',
            'totalEquipos',
            'ordenesPendientes',
            'ordenesEnProceso',
            'ordenesFinalizadas',
            'equiposMantenimientoAlerta',
            'ultimasOrdenes',
            'usuario'
        ));
    }
}
