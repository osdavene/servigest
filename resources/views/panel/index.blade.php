@extends('layouts.app')

@section('titulo', 'Panel Principal')

@section('contenido')
<div>

    <!-- Grid de Métricas Principales -->
    <div class="grid-kpi">
        
        <div class="kpi-card">
            <div class="kpi-icon" style="background: #eff6ff; color: #2563eb;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="kpi-title">Clientes</div>
                <div class="kpi-num">{{ $totalClientes }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: #eef2ff; color: #4f46e5;">
                <i class="fa-solid fa-laptop-medical"></i>
            </div>
            <div>
                <div class="kpi-title">Equipos</div>
                <div class="kpi-num">{{ $totalEquipos }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: #fffbeb; color: #d97706;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="kpi-title">Pendientes</div>
                <div class="kpi-num" style="color: #d97706;">{{ $ordenesPendientes }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: #f0f9ff; color: #0284c7;">
                <i class="fa-solid fa-spinner fa-spin"></i>
            </div>
            <div>
                <div class="kpi-title">En Proceso</div>
                <div class="kpi-num" style="color: #0284c7;">{{ $ordenesEnProceso }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: #ecfdf5; color: #10b981;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="kpi-title">Finalizadas</div>
                <div class="kpi-num" style="color: #10b981;">{{ $ordenesFinalizadas }}</div>
            </div>
        </div>

    </div>

    <!-- Sección de Alertas de Mantenimiento Preventivo -->
    @if($equiposMantenimientoAlerta->isNotEmpty())
        <div class="alert-maintenance-box">
            <div class="alert-header">
                <div class="alert-header-info">
                    <div class="alert-icon-bell">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 900; color: #78350f;">Alertas de Mantenimiento Preventivo</h3>
                        <p style="font-size: 12px; color: #92400e; margin-top: 2px;">Equipos con fecha próxima o vencida para contactar al cliente.</p>
                    </div>
                </div>
                <a href="{{ route('equipos.index', ['alerta_mantenimiento' => 1]) }}" class="btn btn-outline" style="background: #ffffff; border-color: #fde68a; font-size: 11px;">
                    Ver todos los equipos
                </a>
            </div>

            <div class="alert-grid">
                @foreach($equiposMantenimientoAlerta as $eq)
                    <div class="alert-card-item">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fef3c7; color: #b45309; padding: 2px 8px; border-radius: 6px;">
                                    {{ $eq->categoria?->nombre ?? 'General' }}
                                </span>
                                @if($eq->requiere_mantenimiento)
                                    <span class="badge badge-danger" style="font-size: 9px;">¡Vencido!</span>
                                @else
                                    <span class="badge badge-pending" style="font-size: 9px;">En {{ $eq->dias_para_mantenimiento }} días</span>
                                @endif
                            </div>
                            <strong style="font-size: 14px; color: #0f172a; display: block;">{{ $eq->marca }} {{ $eq->modelo }}</strong>
                            <p style="font-size: 12px; color: #64748b; margin-top: 4px;">Cliente: <strong style="color: #334155;">{{ $eq->cliente?->nombre_completo }}</strong></p>
                            <p style="font-size: 11px; color: #94a3b8;">Fecha: {{ $eq->fecha_proximo_mantenimiento?->format('d/m/Y') }}</p>
                        </div>

                        <div style="margin-top: 14px; padding-top: 10px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                            <a href="{{ $eq->cliente?->enlace_whatsapp }}" target="_blank" class="btn btn-emerald" style="padding: 6px 12px; font-size: 11px;">
                                <i class="fa-brands fa-whatsapp"></i> WhatsApp
                            </a>
                            <a href="{{ route('ordenes.create', ['cliente_id' => $eq->cliente_id]) }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 11px;">
                                + Abrir Orden
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Tabla de Órdenes Recientes -->
    <div class="table-card">
        <div class="card-header" style="padding: 20px 24px; margin-bottom: 0;">
            <div>
                <h3>Últimas Órdenes de Trabajo</h3>
                <p>Historial reciente de servicios y recepciones técnicas.</p>
            </div>
            <a href="{{ route('ordenes.index') }}" class="btn btn-outline" style="font-size: 12px;">
                <span>Ver todas las órdenes</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código / Fecha</th>
                        <th>Cliente</th>
                        <th>Equipo</th>
                        <th>Ubicación</th>
                        <th>Técnico</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ultimasOrdenes as $orden)
                        <tr data-href="{{ route('ordenes.show', $orden) }}" class="clickable-row" title="Clic para ver detalles de la orden {{ $orden->codigo_orden }}">
                            <td>
                                <strong style="color: #0284c7; font-size: 13.5px; display: block;">{{ $orden->codigo_orden }}</strong>
                                <span style="font-size: 11px; color: #94a3b8;">{{ $orden->fecha_ingreso?->format('d/m/Y h:i A') }}</span>
                            </td>
                            <td>
                                <strong style="color: #0f172a; display: block;">{{ $orden->cliente?->nombre_completo }}</strong>
                                <a href="{{ $orden->cliente?->enlace_whatsapp }}" target="_blank" style="font-size: 11px; color: #10b981; text-decoration: none; font-weight: 600;">
                                    <i class="fa-brands fa-whatsapp"></i> {{ $orden->cliente?->telefono }}
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: #334155;">{{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}</div>
                                <span style="font-size: 11px; color: #94a3b8;">{{ $orden->equipo?->categoria?->nombre }}</span>
                            </td>
                            <td>
                                @if($orden->tipo_ubicacion === 'servicio_en_domicilio')
                                    <span class="badge badge-purple">
                                        <i class="fa-solid fa-house"></i> Domicilio
                                    </span>
                                @else
                                    <span class="badge badge-process">
                                        <i class="fa-solid fa-shop"></i> En Taller
                                    </span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: #64748b;">
                                {{ $orden->tecnico?->nombre_completo ?? 'Sin asignar' }}
                            </td>
                            <td>
                                @if($orden->estado === 'finalizado')
                                    <span class="badge badge-finished">Finalizado</span>
                                @elseif($orden->estado === 'en_proceso')
                                    <span class="badge badge-process">En Proceso</span>
                                @else
                                    <span class="badge badge-pending">Pendiente</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline btn-icon" title="Ver Detalles">
                                        <i class="fa-solid fa-eye" style="color: #0284c7;"></i>
                                    </a>
                                    <a href="{{ $orden->enlace_whatsapp_reporte }}" target="_blank" class="btn btn-emerald btn-icon" title="Enviar WhatsApp con PDF">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-clipboard-list" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                No hay órdenes de trabajo registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
