@extends('layouts.app')

@section('titulo', 'Hoja de Vida: ' . $equipo->marca . ' ' . $equipo->modelo)

@section('contenido')
<div>

    <!-- Encabezado del Dispositivo y Acciones Rápidas -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span class="badge" style="background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe;">
                        {{ $equipo->categoria?->nombre ?? 'Equipo' }}
                    </span>
                    <h2 style="font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">
                        {{ $equipo->marca }} {{ $equipo->modelo }}
                    </h2>
                </div>
                <p style="font-size: 13px; color: #64748b; margin-top: 4px;">
                    N° de Serie: <strong style="color: #0f172a; font-family: monospace;">{{ $equipo->numero_serie ?? 'Sin serial registrado' }}</strong>
                    <span style="margin: 0 8px;">•</span>
                    Cliente Titular: <strong style="color: #0284c7;">{{ $equipo->cliente?->nombre_completo }}</strong>
                </p>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('ordenes.create', ['equipo_id' => $equipo->id, 'cliente_id' => $equipo->cliente_id]) }}" class="btn btn-primary" style="box-shadow: 0 6px 14px rgba(2, 132, 199, 0.35);">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>+ Abrir Nueva Orden para este Equipo</span>
                </a>

                <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-outline">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Editar Equipo</span>
                </a>

                <a href="{{ route('equipos.index') }}" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Volver a Equipos</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Layout Grid 2 Columnas Responsivo -->
    <div class="detail-grid-layout">
        
        <!-- COLUMNA IZQUIERDA: DATOS TÉCNICOS Y ALERTAS DE MANTENIMIENTO -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Estado de Mantenimiento Preventivo -->
            <div class="card" style="margin-bottom: 0; border-color: {{ $equipo->requiere_mantenimiento ? '#fca5a5' : '#a7f3d0' }}; background: {{ $equipo->requiere_mantenimiento ? '#fef2f2' : '#f0fdf4' }};">
                <div class="card-header" style="border-bottom-color: {{ $equipo->requiere_mantenimiento ? '#fecaca' : '#bbf7d0' }}; padding-bottom: 12px; margin-bottom: 14px;">
                    <h3 style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: {{ $equipo->requiere_mantenimiento ? '#991b1b' : '#166534' }};">
                        <i class="fa-solid fa-bell"></i> Ciclo de Mantenimiento
                    </h3>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px; font-size: 12.5px;">
                    <div>
                        <span style="color: #64748b; display: block; font-size: 11px; font-weight: 800; text-transform: uppercase;">Último Servicio Realizado:</span>
                        <strong style="color: #0f172a; font-size: 14px;">
                            {{ $equipo->fecha_ultimo_servicio ? \Carbon\Carbon::parse($equipo->fecha_ultimo_servicio)->format('d/m/Y') : 'Sin registro anterior' }}
                        </strong>
                    </div>

                    <div>
                        <span style="color: #64748b; display: block; font-size: 11px; font-weight: 800; text-transform: uppercase;">Próximo Mantenimiento Sugerido:</span>
                        <strong style="color: {{ $equipo->requiere_mantenimiento ? '#dc2626' : '#047857' }}; font-size: 15px;">
                            {{ $equipo->fecha_proximo_mantenimiento ? \Carbon\Carbon::parse($equipo->fecha_proximo_mantenimiento)->format('d/m/Y') : 'No programado' }}
                        </strong>
                    </div>

                    @if($equipo->requiere_mantenimiento)
                        <div style="margin-top: 6px; padding: 8px 10px; background: #ffffff; border-radius: 8px; border: 1px solid #fecaca; font-size: 11.5px; color: #b91c1c; font-weight: 700;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Mantenimiento vencido o programado para hoy.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Datos del Propietario -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-header" style="padding-bottom: 12px; margin-bottom: 14px;">
                    <h3 style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                        <i class="fa-solid fa-user" style="color: #0284c7;"></i> Propietario del Equipo
                    </h3>
                </div>

                <strong style="font-size: 15px; color: #0f172a; display: block;">{{ $equipo->cliente?->nombre_completo }}</strong>
                <span style="font-size: 12px; color: #64748b; font-family: monospace;">{{ $equipo->cliente?->identificacion ?? 'N/A' }}</span>

                <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 6px; font-size: 12px; color: #334155;">
                    <div><i class="fa-solid fa-phone" style="color: #94a3b8; width: 16px;"></i> {{ $equipo->cliente?->telefono }}</div>
                    <div><i class="fa-solid fa-location-dot" style="color: #ef4444; width: 16px;"></i> {{ $equipo->cliente?->direccion }} ({{ $equipo->cliente?->ciudad }})</div>
                </div>

                <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border);">
                    <a href="{{ $equipo->cliente?->enlace_whatsapp }}" target="_blank" class="btn btn-outline" style="width: 100%; font-size: 11px; padding: 6px; color: #10b981; border-color: #a7f3d0; background: #ecfdf5;">
                        <i class="fa-brands fa-whatsapp"></i> Contactar al Cliente por WhatsApp
                    </a>
                </div>
            </div>

            <!-- Observaciones Físicas Permanentes -->
            @if($equipo->observaciones_fisicas)
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="padding-bottom: 10px; margin-bottom: 10px;">
                        <h3 style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                            <i class="fa-solid fa-clipboard-list" style="color: #f59e0b;"></i> Estado Físico Base
                        </h3>
                    </div>
                    <p style="font-size: 12.5px; color: #475569; line-height: 1.4;">
                        {{ $equipo->observaciones_fisicas }}
                    </p>
                </div>
            @endif

        </div>

        <!-- COLUMNA DERECHA: HISTORIAL COMPLETO DE ÓRDENES (HOJA DE VIDA) -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-timeline" style="color: #0284c7;"></i>
                        <span>Hoja de Vida e Historial de Servicios ({{ $equipo->ordenesTrabajo->count() }})</span>
                    </h3>
                    <p>Registro histórico cronológico de todas las reparaciones y mantenimientos realizados a este equipo.</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 18px;">
                @forelse($equipo->ordenesTrabajo as $index => $ot)
                    <div onclick="window.location='{{ route('ordenes.show', $ot) }}'" style="border: 1.5px solid var(--border); border-radius: 16px; padding: 18px; background: var(--card-bg); cursor: pointer; transition: all 0.2s; position: relative;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'" title="Clic para ver detalles de la orden {{ $ot->codigo_orden }}">
                        
                        <!-- Encabezado de la Orden en el Timeline -->
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid var(--border);">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <strong style="font-size: 16px; color: #0284c7;">{{ $ot->codigo_orden }}</strong>
                                
                                @if($ot->estado === 'finalizado')
                                    <span class="badge badge-active"><i class="fa-solid fa-circle-check"></i> Finalizado</span>
                                @elseif($ot->estado === 'en_proceso')
                                    <span class="badge badge-process"><i class="fa-solid fa-screwdriver-wrench"></i> En Proceso</span>
                                @elseif($ot->estado === 'entregado')
                                    <span class="badge badge-slate"><i class="fa-solid fa-box-open"></i> Entregado</span>
                                @else
                                    <span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Pendiente</span>
                                @endif

                                <span style="font-size: 11px; color: #64748b;">
                                    <i class="fa-solid fa-calendar"></i> {{ $ot->fecha_ingreso?->format('d/m/Y') }}
                                </span>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="font-size: 14px; font-weight: 900; color: #0f172a; background: #f8fafc; padding: 4px 10px; border-radius: 8px; border: 1px solid var(--border);">
                                    ${{ number_format($ot->costo_total, 0, ',', '.') }}
                                </span>

                                <a href="{{ route('ordenes.show', $ot) }}" class="btn btn-outline" style="font-size: 11px; padding: 6px 12px;">
                                    <i class="fa-solid fa-eye"></i> Ver Orden
                                </a>
                            </div>
                        </div>

                        <!-- Contenido Técnico de la Intervención -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; font-size: 12.5px;">
                            <div>
                                <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 2px;">Problema Atendido:</span>
                                <p style="color: #0f172a;">{{ $ot->problema_reportado }}</p>
                            </div>

                            <div>
                                <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 2px;">Procedimiento Realizado:</span>
                                <p style="color: #0f172a;">{{ $ot->procedimiento_realizado ?? ($ot->diagnostico ?? 'En diagnóstico...') }}</p>
                            </div>

                            @if($ot->repuestos_usados)
                                <div style="grid-column: 1 / -1;">
                                    <span style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 2px;">Repuestos / Materiales Sustituidos:</span>
                                    <span style="color: #334155; background: #f8fafc; padding: 4px 8px; border-radius: 6px; border: 1px solid var(--border); font-size: 11.5px; display: inline-block;">
                                        <i class="fa-solid fa-cube" style="color: #0284c7;"></i> {{ $ot->repuestos_usados }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Pie de la Orden con Técnico y Evidencias -->
                        <div style="margin-top: 12px; padding-top: 10px; border-top: 1px dashed var(--border); display: flex; align-items: center; justify-content: space-between; font-size: 11px; color: #64748b;">
                            <div>
                                <i class="fa-solid fa-user-gear"></i> Atendido por: <strong>{{ $ot->tecnico?->nombre_completo ?? 'Servicio Técnico' }}</strong>
                            </div>

                            @if($ot->evidencias->isNotEmpty())
                                <div>
                                    <i class="fa-solid fa-camera" style="color: #0284c7;"></i> {{ $ot->evidencias->count() }} Fotografías anexas
                                </div>
                            @endif
                        </div>

                    </div>
                @empty
                    <div style="text-align: center; padding: 48px 20px; color: #94a3b8;">
                        <i class="fa-solid fa-clipboard-list" style="font-size: 36px; margin-bottom: 8px; display: block;"></i>
                        <h4 style="font-size: 15px; font-weight: 800; color: #334155;">Sin órdenes previas registradas</h4>
                        <p style="font-size: 12.5px; margin-top: 4px;">Este equipo aún no tiene intervenciones técnicas archivadas.</p>
                        <a href="{{ route('ordenes.create', ['equipo_id' => $equipo->id, 'cliente_id' => $equipo->cliente_id]) }}" class="btn btn-primary" style="margin-top: 16px;">
                            + Crear Primera Orden de Trabajo
                        </a>
                    </div>
                @endforelse
            </div>

        </div>

    </div>

</div>
@endsection
