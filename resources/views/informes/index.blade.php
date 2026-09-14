@extends('layouts.app')

@section('titulo', 'Informes & Estadísticas Gerenciales')

@section('contenido')
<div>

    <!-- 1. BARRA DE FILTROS TEMPORALES Y ACCIONES DE EXPORTACIÓN -->
    <div class="card" style="padding: 20px 24px; margin-bottom: 24px;">
        <form method="GET" action="{{ route('informes.index') }}" id="formFiltrosInformes">
            
            <!-- Selector de Periodos Rápidos -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid var(--border);">
                <div>
                    <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                        <i class="fa-solid fa-calendar-days" style="color: #0284c7; margin-right: 4px;"></i> Periodo Seleccionado:
                    </span>
                    <strong style="font-size: 16px; color: #0f172a;">{{ $filtros['nombre_periodo'] }}</strong>
                </div>

                <!-- Botones de Exportación Oficial -->
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('informes.pdf', request()->query()) }}" target="_blank" class="btn btn-rose" style="box-shadow: 0 4px 12px rgba(239, 68, 68, 0.25);">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>Informe PDF Oficial</span>
                    </a>

                    <a href="{{ route('informes.csv', request()->query()) }}" class="btn btn-emerald" style="box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Exportar a Excel / CSV</span>
                    </a>
                </div>
            </div>

            <!-- Chips de Periodo y Filtros Avanzados -->
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                
                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                    <button type="submit" name="periodo" value="hoy" class="btn {{ $filtros['periodo'] == 'hoy' ? 'btn-primary' : 'btn-outline' }}" style="padding: 6px 12px; font-size: 11.5px;">
                        Hoy
                    </button>
                    <button type="submit" name="periodo" value="esta_semana" class="btn {{ $filtros['periodo'] == 'esta_semana' ? 'btn-primary' : 'btn-outline' }}" style="padding: 6px 12px; font-size: 11.5px;">
                        Esta Semana
                    </button>
                    <button type="submit" name="periodo" value="este_mes" class="btn {{ $filtros['periodo'] == 'este_mes' ? 'btn-primary' : 'btn-outline' }}" style="padding: 6px 12px; font-size: 11.5px;">
                        Este Mes
                    </button>
                    <button type="submit" name="periodo" value="mes_anterior" class="btn {{ $filtros['periodo'] == 'mes_anterior' ? 'btn-primary' : 'btn-outline' }}" style="padding: 6px 12px; font-size: 11.5px;">
                        Mes Anterior
                    </button>
                    <button type="submit" name="periodo" value="este_ano" class="btn {{ $filtros['periodo'] == 'este_ano' ? 'btn-primary' : 'btn-outline' }}" style="padding: 6px 12px; font-size: 11.5px;">
                        Año {{ date('Y') }}
                    </button>
                </div>

                <!-- Filtros Desplegables -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <select name="tecnico_id" onchange="this.form.submit()" class="select-control" style="font-size: 12px; padding: 6px 12px;">
                        <option value="">Todos los Técnicos</option>
                        @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}" {{ $filtros['tecnico_id'] == $tec->id ? 'selected' : '' }}>{{ $tec->nombre_completo }}</option>
                        @endforeach
                    </select>

                    <select name="categoria_id" onchange="this.form.submit()" class="select-control" style="font-size: 12px; padding: 6px 12px;">
                        <option value="">Todas las Categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ $filtros['categoria_id'] == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>

                    <select name="tipo_ubicacion" onchange="this.form.submit()" class="select-control" style="font-size: 12px; padding: 6px 12px;">
                        <option value="">Todas las Modalidades</option>
                        <option value="ingresado_al_taller" {{ $filtros['tipo_ubicacion'] == 'ingresado_al_taller' ? 'selected' : '' }}>🏢 En Taller</option>
                        <option value="servicio_en_domicilio" {{ $filtros['tipo_ubicacion'] == 'servicio_en_domicilio' ? 'selected' : '' }}>🏠 Domicilio</option>
                    </select>
                </div>

            </div>

            <!-- Selector de Rango Personalizado de Fechas (Opcional) -->
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px dashed var(--border); display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-size: 12px;">
                <span style="color: #64748b; font-weight: 700;">Rango Personalizado:</span>
                <input type="hidden" name="periodo" value="personalizado" id="inputPeriodoPersonalizado">
                
                <input type="date" name="fecha_desde" value="{{ $filtros['fecha_desde'] }}" class="form-input-text" style="width: auto; padding: 6px 10px; font-size: 12px;">
                <span style="color: #94a3b8;">hasta</span>
                <input type="date" name="fecha_hasta" value="{{ $filtros['fecha_hasta'] }}" class="form-input-text" style="width: auto; padding: 6px 10px; font-size: 12px;">

                <button type="submit" onclick="document.getElementById('inputPeriodoPersonalizado').disabled=false;" class="btn btn-dark" style="padding: 6px 14px; font-size: 11.5px;">
                    Aplicar Rango
                </button>
            </div>

        </form>
    </div>

    <!-- 2. GRID DE KPIS FINANCIEROS Y OPERATIVOS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 24px;">
        
        <!-- Facturación Total -->
        <div class="card" style="margin-bottom: 0; background: linear-gradient(135deg, #047857 0%, #065f46 100%); color: #ffffff; border: none; box-shadow: 0 10px 20px rgba(4, 120, 87, 0.25);">
            <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85;">Facturación Total</span>
            <strong style="font-size: 26px; font-weight: 900; display: block; margin-top: 4px;">${{ number_format($totalIngresos, 0, ',', '.') }}</strong>
            <div style="margin-top: 10px; font-size: 11px; opacity: 0.9; display: flex; align-items: center; justify-content: space-between;">
                <span>{{ $totalCerradas }} órdenes cobradas</span>
                <span>100% Recaudado</span>
            </div>
        </div>

        <!-- Mano de Obra -->
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b;">Mano de Obra</span>
                <i class="fa-solid fa-screwdriver-wrench" style="color: #0284c7; font-size: 16px;"></i>
            </div>
            <strong style="font-size: 22px; font-weight: 900; color: #0f172a; display: block; margin-top: 4px;">${{ number_format($totalManoObra, 0, ',', '.') }}</strong>
            <span style="font-size: 11px; color: #64748b; margin-top: 6px; display: block;">
                {{ $totalIngresos > 0 ? round(($totalManoObra / $totalIngresos) * 100, 1) : 0 }}% del total facturado
            </span>
        </div>

        <!-- Repuestos / Refacciones -->
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b;">Repuestos</span>
                <i class="fa-solid fa-cube" style="color: #8b5cf6; font-size: 16px;"></i>
            </div>
            <strong style="font-size: 22px; font-weight: 900; color: #0f172a; display: block; margin-top: 4px;">${{ number_format($totalRepuestos, 0, ',', '.') }}</strong>
            <span style="font-size: 11px; color: #64748b; margin-top: 6px; display: block;">
                {{ $totalIngresos > 0 ? round(($totalRepuestos / $totalIngresos) * 100, 1) : 0 }}% refacciones aplicadas
            </span>
        </div>

        <!-- Ticket Promedio -->
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b;">Ticket Promedio</span>
                <i class="fa-solid fa-receipt" style="color: #f59e0b; font-size: 16px;"></i>
            </div>
            <strong style="font-size: 22px; font-weight: 900; color: #0f172a; display: block; margin-top: 4px;">${{ number_format($ticketPromedio, 0, ',', '.') }}</strong>
            <span style="font-size: 11px; color: #64748b; margin-top: 6px; display: block;">
                Promedio por servicio cobrado
            </span>
        </div>

        <!-- Tiempo Promedio de Entrega -->
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b;">Tiempo Promedio</span>
                <i class="fa-solid fa-stopwatch" style="color: #ec4899; font-size: 16px;"></i>
            </div>
            <strong style="font-size: 22px; font-weight: 900; color: #0f172a; display: block; margin-top: 4px;">{{ $tiempoPromedioDias }} Días</strong>
            <span style="font-size: 11px; color: #64748b; margin-top: 6px; display: block;">
                Ciclo desde recepción a entrega
            </span>
        </div>

        <!-- Tasa de Efectividad -->
        <div class="card" style="margin-bottom: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b;">Tasa de Cierre</span>
                <i class="fa-solid fa-chart-line" style="color: #10b981; font-size: 16px;"></i>
            </div>
            <strong style="font-size: 22px; font-weight: 900; color: #10b981; display: block; margin-top: 4px;">{{ $tasaEfectividad }}%</strong>
            <span style="font-size: 11px; color: #64748b; margin-top: 6px; display: block;">
                {{ $totalCerradas }} de {{ $totalOrdenes }} órdenes exitosas
            </span>
        </div>

    </div>

    <!-- 3. DESGLOSE POR CATEGORÍA Y MODALIDADES DE SERVICIO -->
    <div class="grid-2-col" style="margin-bottom: 24px;">
        
        <!-- Desglose de Facturación por Categoría de Equipo -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-tags" style="color: #0284c7;"></i>
                        <span>Facturación por Tipo de Dispositivo</span>
                    </h3>
                    <p>Líneas de negocio con mayor rentabilidad y volumen de servicio.</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 14px;">
                @forelse($porCategoria as $catData)
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; margin-bottom: 4px;">
                            <strong style="color: #0f172a;">{{ $catData['categoria'] }} ({{ $catData['cantidad'] }} {{ Str::plural('servicio', $catData['cantidad']) }})</strong>
                            <span style="font-weight: 900; color: #047857;">${{ number_format($catData['facturado'], 0, ',', '.') }} ({{ $catData['porcentaje'] }}%)</span>
                        </div>
                        <div style="width: 100%; height: 8px; background: #f1f5f9; border-radius: 6px; overflow: hidden;">
                            <div style="width: {{ $catData['porcentaje'] }}%; height: 100%; background: linear-gradient(90deg, #0284c7, #2563eb); border-radius: 6px;"></div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 24px; color: #94a3b8;">
                        <p style="font-size: 13px;">No hay registros en este periodo.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Resumen de Estados y Modalidad de Atención -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-pie-chart" style="color: #8b5cf6;"></i>
                        <span>Modalidades y Estados de Órdenes</span>
                    </h3>
                    <p>Distribución operativa de los trabajos en el taller.</p>
                </div>
            </div>

            <!-- Modalidades (Taller vs Domicilio) -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div style="padding: 14px; background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 14px;">
                    <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #0369a1; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-shop"></i> En Taller
                    </span>
                    <strong style="font-size: 18px; color: #0c4a6e; display: block; margin-top: 4px;">{{ $conteoTaller }} Órdenes</strong>
                    <span style="font-size: 11.5px; color: #0369a1; font-weight: 700;">${{ number_format($ingresosTaller, 0, ',', '.') }} Facturado</span>
                </div>

                <div style="padding: 14px; background: #f5f3ff; border: 1.5px solid #ddd6fe; border-radius: 14px;">
                    <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #6d28d9; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-house"></i> En Domicilio
                    </span>
                    <strong style="font-size: 18px; color: #4c1d95; display: block; margin-top: 4px;">{{ $conteoDomicilio }} Órdenes</strong>
                    <span style="font-size: 11.5px; color: #6d28d9; font-weight: 700;">${{ number_format($ingresosDomicilio, 0, ',', '.') }} Facturado</span>
                </div>
            </div>

            <!-- Desglose por Estado -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); gap: 8px; text-align: center;">
                <div style="padding: 10px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border);">
                    <span class="badge badge-active" style="font-size: 9px;">Finalizadas</span>
                    <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 2px;">{{ $conteoFinalizadas }}</strong>
                </div>

                <div style="padding: 10px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border);">
                    <span class="badge badge-slate" style="font-size: 9px;">Entregadas</span>
                    <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 2px;">{{ $conteoEntregadas }}</strong>
                </div>

                <div style="padding: 10px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border);">
                    <span class="badge badge-process" style="font-size: 9px;">En Proceso</span>
                    <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 2px;">{{ $conteoEnProceso }}</strong>
                </div>

                <div style="padding: 10px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border);">
                    <span class="badge badge-pending" style="font-size: 9px;">Pendientes</span>
                    <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 2px;">{{ $conteoPendientes }}</strong>
                </div>
            </div>

        </div>

    </div>

    <!-- 4. TABLA DE PRODUCTIVIDAD Y FACTURACIÓN POR TÉCNICO -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header">
            <div>
                <h3 style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-users-gear" style="color: #10b981;"></i>
                    <span>Rendimiento y Productividad por Técnico</span>
                </h3>
                <p>Órdenes atendidas, trabajos entregados e ingresos generados por cada colaborador.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Técnico Especialista</th>
                        <th style="text-align: center;">Asignadas</th>
                        <th style="text-align: center;">Cerradas</th>
                        <th style="text-align: center;">En Proceso</th>
                        <th>Mano de Obra ($)</th>
                        <th>Total Facturado ($)</th>
                        <th style="text-align: center;">Efectividad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($porTecnico as $itemTec)
                        @php
                            $efectividadTec = $itemTec['total_asignadas'] > 0 ? round(($itemTec['cerradas'] / $itemTec['total_asignadas']) * 100, 1) : 0;
                        @endphp
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0369a1; display: flex; align-items: center; justify-content: center; font-weight: 800;">
                                        {{ strtoupper(substr($itemTec['tecnico']->nombre, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong style="color: #0f172a; font-size: 13.5px; display: block;">{{ $itemTec['tecnico']->nombre_completo }}</strong>
                                        <span style="font-size: 11px; color: #64748b;">{{ $itemTec['tecnico']->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align: center; font-weight: 700;">{{ $itemTec['total_asignadas'] }}</td>
                            <td style="text-align: center;">
                                <span class="badge badge-active" style="font-size: 10px;">{{ $itemTec['cerradas'] }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-process" style="font-size: 10px;">{{ $itemTec['en_proceso'] }}</span>
                            </td>
                            <td style="font-weight: 700; color: #334155;">
                                ${{ number_format($itemTec['mano_obra'], 0, ',', '.') }}
                            </td>
                            <td style="font-weight: 900; color: #047857; font-size: 14px;">
                                ${{ number_format($itemTec['ingresos_generados'], 0, ',', '.') }}
                            </td>
                            <td style="text-align: center;">
                                <span class="badge {{ $efectividadTec >= 70 ? 'badge-active' : 'badge-pending' }}" style="font-size: 10px;">
                                    {{ $efectividadTec }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: #94a3b8;">
                                No hay técnicos asignados en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 5. LIBRO DE ÓRDENES CERRADAS EN EL PERIODO -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <div>
                <h3 style="display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-list-check" style="color: #0284c7;"></i>
                    <span>Detalle de Servicios del Periodo ({{ $totalOrdenes }})</span>
                </h3>
                <p>Lista cronológica de las órdenes atendidas con su respectiva liquidación.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código / Fecha</th>
                        <th>Cliente</th>
                        <th>Equipo / Serie</th>
                        <th>Técnico</th>
                        <th>M. Obra</th>
                        <th>Repuestos</th>
                        <th>Total ($)</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenesRecientes as $ot)
                        <tr>
                            <td>
                                <strong style="color: #0284c7; font-size: 13.5px;">{{ $ot->codigo_orden }}</strong>
                                <span style="font-size: 11px; color: #64748b; display: block;">{{ $ot->fecha_ingreso?->format('d/m/Y') }}</span>
                            </td>
                            <td>
                                <strong style="color: #0f172a; font-size: 13px; display: block;">{{ $ot->cliente?->nombre_completo }}</strong>
                                <span style="font-size: 11px; color: #64748b;">{{ $ot->cliente?->telefono }}</span>
                            </td>
                            <td>
                                <span style="color: #334155; font-weight: 700; font-size: 13px; display: block;">{{ $ot->equipo?->marca }} {{ $ot->equipo?->modelo }}</span>
                                <span style="font-size: 11px; color: #94a3b8;">{{ $ot->equipo?->categoria?->nombre ?? 'General' }}</span>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: #334155;">{{ $ot->tecnico?->nombre_completo ?? 'Sin asignar' }}</span>
                            </td>
                            <td>${{ number_format($ot->costo_mano_obra, 0, ',', '.') }}</td>
                            <td>${{ number_format($ot->costo_repuestos, 0, ',', '.') }}</td>
                            <td>
                                <strong style="font-size: 14px; color: #047857;">${{ number_format($ot->costo_total, 0, ',', '.') }}</strong>
                            </td>
                            <td>
                                @if($ot->estado === 'finalizado')
                                    <span class="badge badge-active" style="font-size: 9px;">Finalizado</span>
                                @elseif($ot->estado === 'entregado')
                                    <span class="badge badge-slate" style="font-size: 9px;">Entregado</span>
                                @elseif($ot->estado === 'en_proceso')
                                    <span class="badge badge-process" style="font-size: 9px;">En Proceso</span>
                                @else
                                    <span class="badge badge-pending" style="font-size: 9px;">Pendiente</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('ordenes.show', $ot) }}" class="btn btn-outline btn-icon" title="Ver Orden">
                                    <i class="fa-solid fa-eye" style="color: #0284c7;"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #94a3b8;">
                                No se encontraron órdenes en el rango de fechas seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
