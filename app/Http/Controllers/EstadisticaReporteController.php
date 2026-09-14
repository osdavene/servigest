<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\OrdenTrabajo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstadisticaReporteController extends Controller
{
    /**
     * Dashboard principal de estadísticas y analíticas del taller.
     */
    public function index(Request $request)
    {
        $filtros = $this->obtenerFiltros($request);
        $datos = $this->calcularEstadisticas($filtros);

        $tecnicos = Usuario::where('rol', 'tecnico')->orderBy('nombre')->get();
        $categorias = Categoria::orderBy('nombre')->get();

        return view('informes.index', array_merge($datos, [
            'filtros' => $filtros,
            'tecnicos' => $tecnicos,
            'categorias' => $categorias,
        ]));
    }

    /**
     * Exporta un informe ejecutivo oficial en formato PDF membretado.
     */
    public function exportarPdf(Request $request)
    {
        $taller = auth()->user()->taller;
        $filtros = $this->obtenerFiltros($request);
        $datos = $this->calcularEstadisticas($filtros);

        $pdf = Pdf::loadView('informes.pdf_ejecutivo', array_merge($datos, [
            'taller' => $taller,
            'filtros' => $filtros,
            'fechaGeneracion' => now(),
            'generadoPor' => auth()->user()->nombre_completo,
        ]))->setPaper('letter', 'portrait');

        $nombreArchivo = 'informe_gerencial_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($nombreArchivo);
    }

    /**
     * Exporta el desglose de órdenes a un archivo CSV compatible con Excel.
     */
    public function exportarCsv(Request $request): StreamedResponse
    {
        $filtros = $this->obtenerFiltros($request);
        $ordenes = $this->construirConsulta($filtros)
            ->with(['cliente', 'equipo.categoria', 'tecnico'])
            ->latest('fecha_ingreso')
            ->get();

        $nombreArchivo = 'reporte_ordenes_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nombreArchivo}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($ordenes) {
            $archivo = fopen('php://output', 'w');
            // BOM UTF-8 para que Excel lo abra con acentos correctos
            fprintf($archivo, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados
            fputcsv($archivo, [
                'Codigo OT',
                'Fecha Ingreso',
                'Fecha Finalizacion',
                'Cliente',
                'Documento Cliente',
                'Telefono',
                'Equipo / Dispositivo',
                'Categoria',
                'Numero Serie',
                'Tecnico Asignado',
                'Modalidad',
                'Estado',
                'Mano de Obra ($)',
                'Repuestos ($)',
                'Total Facturado ($)',
                'Falla Reportada',
                'Diagnostico',
                'Procedimiento Realizado'
            ], ';');

            foreach ($ordenes as $ot) {
                fputcsv($archivo, [
                    $ot->codigo_orden,
                    $ot->fecha_ingreso ? $ot->fecha_ingreso->format('d/m/Y H:i') : '',
                    $ot->fecha_finalizacion ? $ot->fecha_finalizacion->format('d/m/Y H:i') : '',
                    $ot->cliente?->nombre_completo ?? 'N/A',
                    $ot->cliente?->identificacion ?? '',
                    $ot->cliente?->telefono ?? '',
                    ($ot->equipo?->marca . ' ' . $ot->equipo?->modelo),
                    $ot->equipo?->categoria?->nombre ?? 'General',
                    $ot->equipo?->numero_serie ?? '',
                    $ot->tecnico?->nombre_completo ?? 'Sin asignar',
                    str_replace('_', ' ', $ot->tipo_ubicacion),
                    ucfirst(str_replace('_', ' ', $ot->estado)),
                    number_format($ot->costo_mano_obra, 2, '.', ''),
                    number_format($ot->costo_repuestos, 2, '.', ''),
                    number_format($ot->costo_total, 2, '.', ''),
                    $ot->problema_reportado,
                    $ot->diagnostico ?? '',
                    $ot->procedimiento_realizado ?? '',
                ], ';');
            }

            fclose($archivo);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Procesa y normaliza los parámetros de filtrado.
     */
    private function obtenerFiltros(Request $request): array
    {
        $periodo = $request->input('periodo', 'este_mes');
        $fechaDesde = $request->input('fecha_desde');
        $fechaHasta = $request->input('fecha_hasta');
        $tecnicoId = $request->input('tecnico_id');
        $categoriaId = $request->input('categoria_id');
        $tipoUbicacion = $request->input('tipo_ubicacion');
        $estado = $request->input('estado');

        $ahora = Carbon::now();

        switch ($periodo) {
            case 'hoy':
                $inicio = $ahora->copy()->startOfDay();
                $fin = $ahora->copy()->endOfDay();
                $nombrePeriodo = 'Hoy (' . $ahora->format('d/m/Y') . ')';
                break;
            case 'esta_semana':
                $inicio = $ahora->copy()->startOfWeek();
                $fin = $ahora->copy()->endOfWeek();
                $nombrePeriodo = 'Esta Semana (' . $inicio->format('d/m') . ' - ' . $fin->format('d/m/Y') . ')';
                break;
            case 'mes_anterior':
                $inicio = $ahora->copy()->subMonth()->startOfMonth();
                $fin = $ahora->copy()->subMonth()->endOfMonth();
                $nombrePeriodo = 'Mes Anterior (' . $inicio->translatedFormat('F Y') . ')';
                break;
            case 'este_ano':
                $inicio = $ahora->copy()->startOfYear();
                $fin = $ahora->copy()->endOfYear();
                $nombrePeriodo = 'Año ' . $ahora->year;
                break;
            case 'personalizado':
                $inicio = $fechaDesde ? Carbon::parse($fechaDesde)->startOfDay() : $ahora->copy()->startOfMonth();
                $fin = $fechaHasta ? Carbon::parse($fechaHasta)->endOfDay() : $ahora->copy()->endOfDay();
                $nombrePeriodo = 'Del ' . $inicio->format('d/m/Y') . ' al ' . $fin->format('d/m/Y');
                break;
            case 'este_mes':
            default:
                $periodo = 'este_mes';
                $inicio = $ahora->copy()->startOfMonth();
                $fin = $ahora->copy()->endOfMonth();
                $nombrePeriodo = 'Este Mes (' . $ahora->translatedFormat('F Y') . ')';
                break;
        }

        return [
            'periodo' => $periodo,
            'inicio' => $inicio,
            'fin' => $fin,
            'nombre_periodo' => $nombrePeriodo,
            'fecha_desde' => $inicio->format('Y-m-d'),
            'fecha_hasta' => $fin->format('Y-m-d'),
            'tecnico_id' => $tecnicoId,
            'categoria_id' => $categoriaId,
            'tipo_ubicacion' => $tipoUbicacion,
            'estado' => $estado,
        ];
    }

    /**
     * Construye la consulta base aplicando los filtros seleccionados.
     */
    private function construirConsulta(array $filtros)
    {
        return OrdenTrabajo::query()
            ->whereBetween('fecha_ingreso', [$filtros['inicio'], $filtros['fin']])
            ->when($filtros['tecnico_id'], fn($q, $t) => $q->where('tecnico_asignado_id', $t))
            ->when($filtros['categoria_id'], fn($q, $c) => $q->whereHas('equipo', fn($qe) => $qe->where('categoria_id', $c)))
            ->when($filtros['tipo_ubicacion'], fn($q, $u) => $q->where('tipo_ubicacion', $u))
            ->when($filtros['estado'], fn($q, $e) => $q->where('estado', $e));
    }

    /**
     * Calcula métricas financieras, operativas y agrupaciones por técnico y categoría.
     */
    private function calcularEstadisticas(array $filtros): array
    {
        $ordenes = $this->construirConsulta($filtros)
            ->with(['cliente', 'equipo.categoria', 'tecnico'])
            ->get();

        $ordenesCerradas = $ordenes->whereIn('estado', ['finalizado', 'entregado']);

        // 1. Métricas Financieras
        $totalIngresos = (float)$ordenesCerradas->sum('costo_total');
        $totalManoObra = (float)$ordenesCerradas->sum('costo_mano_obra');
        $totalRepuestos = (float)$ordenesCerradas->sum('costo_repuestos');
        $ticketPromedio = $ordenesCerradas->count() > 0 ? ($totalIngresos / $ordenesCerradas->count()) : 0;

        // 2. Métricas Operativas y Volúmenes
        $totalOrdenes = $ordenes->count();
        $conteoFinalizadas = $ordenes->where('estado', 'finalizado')->count();
        $conteoEntregadas = $ordenes->where('estado', 'entregado')->count();
        $conteoEnProceso = $ordenes->where('estado', 'en_proceso')->count();
        $conteoPendientes = $ordenes->where('estado', 'pendiente')->count();
        $conteoCanceladas = $ordenes->where('estado', 'cancelado')->count();

        $totalCerradas = $conteoFinalizadas + $conteoEntregadas;
        $tasaEfectividad = $totalOrdenes > 0 ? round(($totalCerradas / $totalOrdenes) * 100, 1) : 0;

        // 3. Tiempo Promedio de Reparación (días)
        $duracionesEnDias = [];
        foreach ($ordenesCerradas as $ot) {
            if ($ot->fecha_ingreso && $ot->fecha_finalizacion) {
                $duracionesEnDias[] = $ot->fecha_ingreso->diffInHours($ot->fecha_finalizacion) / 24;
            }
        }
        $tiempoPromedioDias = count($duracionesEnDias) > 0 ? round(array_sum($duracionesEnDias) / count($duracionesEnDias), 1) : 0;

        // 4. Desglose por Categoría de Dispositivo
        $porCategoria = [];
        foreach ($ordenes->groupBy('equipo.categoria.nombre') as $nombreCat => $grupo) {
            $nombre = $nombreCat ?: 'Sin Categoría';
            $facturadoCat = $grupo->whereIn('estado', ['finalizado', 'entregado'])->sum('costo_total');
            $porcentaje = $totalIngresos > 0 ? round(($facturadoCat / $totalIngresos) * 100, 1) : 0;

            $porCategoria[] = [
                'categoria' => $nombre,
                'cantidad' => $grupo->count(),
                'facturado' => (float)$facturadoCat,
                'porcentaje' => $porcentaje,
            ];
        }
        usort($porCategoria, fn($a, $b) => $b['facturado'] <=> $a['facturado']);

        // 5. Productividad y Rendimiento por Técnico
        $porTecnico = [];
        $tecnicosTaller = Usuario::where('rol', 'tecnico')->get();
        foreach ($tecnicosTaller as $tec) {
            $ordenesTec = $ordenes->where('tecnico_asignado_id', $tec->id);
            $cerradasTec = $ordenesTec->whereIn('estado', ['finalizado', 'entregado']);
            $ingresosTec = $cerradasTec->sum('costo_total');
            $manoObraTec = $cerradasTec->sum('costo_mano_obra');

            $porTecnico[] = [
                'tecnico' => $tec,
                'total_asignadas' => $ordenesTec->count(),
                'cerradas' => $cerradasTec->count(),
                'en_proceso' => $ordenesTec->where('estado', 'en_proceso')->count(),
                'ingresos_generados' => (float)$ingresosTec,
                'mano_obra' => (float)$manoObraTec,
            ];
        }
        usort($porTecnico, fn($a, $b) => $b['ingresos_generados'] <=> $a['ingresos_generados']);

        // 6. Modalidad del Servicio (Taller vs Domicilio)
        $conteoTaller = $ordenes->where('tipo_ubicacion', 'ingresado_al_taller')->count();
        $conteoDomicilio = $ordenes->where('tipo_ubicacion', 'servicio_en_domicilio')->count();
        $ingresosTaller = $ordenes->where('tipo_ubicacion', 'ingresado_al_taller')->whereIn('estado', ['finalizado', 'entregado'])->sum('costo_total');
        $ingresosDomicilio = $ordenes->where('tipo_ubicacion', 'servicio_en_domicilio')->whereIn('estado', ['finalizado', 'entregado'])->sum('costo_total');

        return [
            'totalIngresos' => $totalIngresos,
            'totalManoObra' => $totalManoObra,
            'totalRepuestos' => $totalRepuestos,
            'ticketPromedio' => $ticketPromedio,
            'totalOrdenes' => $totalOrdenes,
            'totalCerradas' => $totalCerradas,
            'conteoFinalizadas' => $conteoFinalizadas,
            'conteoEntregadas' => $conteoEntregadas,
            'conteoEnProceso' => $conteoEnProceso,
            'conteoPendientes' => $conteoPendientes,
            'conteoCanceladas' => $conteoCanceladas,
            'tasaEfectividad' => $tasaEfectividad,
            'tiempoPromedioDias' => $tiempoPromedioDias,
            'porCategoria' => $porCategoria,
            'porTecnico' => $porTecnico,
            'conteoTaller' => $conteoTaller,
            'conteoDomicilio' => $conteoDomicilio,
            'ingresosTaller' => $ingresosTaller,
            'ingresosDomicilio' => $ingresosDomicilio,
            'ordenesRecientes' => $ordenes->take(20),
        ];
    }
}
