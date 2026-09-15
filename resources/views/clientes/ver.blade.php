@extends('layouts.app')

@section('titulo', 'Cliente: ' . $cliente->nombre_completo)

@section('contenido')
<div>

    <!-- Encabezado del Cliente y Botones Rápidos -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: #e0f2fe; color: #0369a1; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 900; flex-shrink: 0;">
                    {{ strtoupper(substr($cliente->nombre_completo, 0, 1)) }}
                </div>

                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h2 style="font-size: 22px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">{{ $cliente->nombre_completo }}</h2>
                        <span class="badge badge-slate" style="font-family: monospace;">{{ $cliente->identificacion ?? 'Sin documento' }}</span>
                    </div>

                    <p style="font-size: 13px; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-location-dot" style="color: #ef4444;"></i>
                        <span>{{ $cliente->direccion }}{{ $cliente->barrio ? ', ' . $cliente->barrio : '' }} ({{ $cliente->ciudad }})</span>
                    </p>
                </div>
            </div>

            <!-- Botones Rápidos de Comunicación y Portal -->
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                <a href="{{ $cliente->url_portal }}" target="_blank" class="btn btn-primary" style="background: #0284c7; box-shadow: 0 6px 14px rgba(2, 132, 199, 0.3);">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    <span>Portal Cliente B2B</span>
                </a>

                <a href="{{ $cliente->enlace_whatsapp }}" target="_blank" class="btn btn-emerald" style="box-shadow: 0 6px 14px rgba(16, 185, 129, 0.3);">
                    <i class="fa-brands fa-whatsapp" style="font-size: 16px;"></i>
                    <span>WhatsApp Directo</span>
                </a>

                <a href="{{ $cliente->enlace_google_maps }}" target="_blank" class="btn btn-primary">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <span>Google Maps</span>
                </a>

                <a href="{{ $cliente->enlace_waze }}" target="_blank" class="btn btn-outline" style="color: #0891b2; border-color: #a5f3fc; background: #ecfeff;">
                    <i class="fa-brands fa-waze"></i>
                    <span>Waze</span>
                </a>

                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-dark">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Editar</span>
                </a>

                <a href="{{ route('clientes.index') }}" class="btn btn-outline">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Volver</span>
                </a>
            </div>

        </div>
    </div>

    <!-- Grid de Dispositivos y Órdenes del Cliente Responsivo -->
    <div class="grid-2-col">
        
        <!-- Equipos Registrados del Cliente -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-laptop-medical" style="color: #0284c7;"></i>
                        <span>Dispositivos del Cliente ({{ $cliente->equipos->count() }})</span>
                    </h3>
                    <p>Equipos registrados y asociados a este titular.</p>
                </div>

                <a href="{{ route('equipos.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-outline" style="font-size: 11px; padding: 6px 12px; color: #0284c7;">
                    + Registrar Equipo
                </a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($cliente->equipos as $eq)
                    <div onclick="window.location='{{ route('equipos.show', $eq) }}'" style="border: 1px solid var(--border); border-radius: 14px; padding: 14px; display: flex; align-items: center; justify-content: space-between; background: var(--card-bg); cursor: pointer; transition: all 0.15s ease;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'" title="Clic para ver hoja de vida de {{ $eq->marca }} {{ $eq->modelo }}">
                        <div>
                            <span class="badge" style="background: #f5f3ff; color: #7c3aed; font-size: 9px; margin-bottom: 4px;">
                                {{ $eq->categoria?->nombre ?? 'Equipo' }}
                            </span>
                            <strong style="font-size: 14px; color: #0f172a; display: block;">{{ $eq->marca }} {{ $eq->modelo }}</strong>
                            <span style="font-size: 11px; color: #64748b; font-family: monospace;">Serie: {{ $eq->numero_serie ?? 'N/A' }}</span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 6px;">
                            <a href="{{ route('equipos.show', $eq) }}" class="btn btn-outline" style="font-size: 11px; padding: 6px 10px;">
                                Hoja de Vida
                            </a>
                            <a href="{{ route('ordenes.create', ['equipo_id' => $eq->id, 'cliente_id' => $cliente->id]) }}" class="btn btn-primary" style="font-size: 11px; padding: 6px 10px;">
                                + Orden
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 32px; color: #94a3b8;">
                        <p style="font-size: 13px;">No tiene equipos registrados aún.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Órdenes de Servicio del Cliente -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-clipboard-list" style="color: #10b981;"></i>
                        <span>Historial de Servicios ({{ $cliente->ordenesTrabajo->count() }})</span>
                    </h3>
                    <p>Órdenes técnicas solicitadas por este cliente.</p>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @forelse($cliente->ordenesTrabajo as $ot)
                    <div onclick="window.location='{{ route('ordenes.show', $ot) }}'" style="border: 1px solid var(--border); border-radius: 14px; padding: 14px; display: flex; align-items: center; justify-content: space-between; background: var(--card-bg); cursor: pointer; transition: all 0.15s ease;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border)'" title="Clic para ver detalles de la orden {{ $ot->codigo_orden }}">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <strong style="color: #0284c7; font-size: 14px;">{{ $ot->codigo_orden }}</strong>
                                @if($ot->estado === 'finalizado')
                                    <span class="badge badge-active" style="font-size: 9px;">Finalizado</span>
                                @elseif($ot->estado === 'en_proceso')
                                    <span class="badge badge-process" style="font-size: 9px;">En Proceso</span>
                                @else
                                    <span class="badge badge-pending" style="font-size: 9px;">Pendiente</span>
                                @endif
                            </div>
                            <span style="font-size: 12px; color: #334155; display: block; margin-top: 2px;">{{ $ot->equipo?->marca }} {{ $ot->equipo?->modelo }}</span>
                            <span style="font-size: 11px; color: #64748b;">{{ $ot->fecha_ingreso?->format('d/m/Y') }}</span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <strong style="font-size: 14px; color: #0f172a;">${{ number_format($ot->costo_total, 0, ',', '.') }}</strong>
                            <a href="{{ route('ordenes.show', $ot) }}" class="btn btn-outline" style="font-size: 11px; padding: 6px 10px;">
                                Ver
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 32px; color: #94a3b8;">
                        <p style="font-size: 13px;">No tiene órdenes de servicio registradas.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
