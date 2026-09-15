@extends('layouts.app')

@section('titulo', 'Órdenes de Trabajo')

@section('contenido')
<div>

    <!-- Filtros de Órdenes -->
    <div class="card" style="padding: 18px 24px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('ordenes.index') }}" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; flex-wrap: wrap;">
                <div class="search-input-wrapper" style="min-width: 280px; flex: 1;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" 
                           name="buscar" 
                           value="{{ $busqueda ?? '' }}" 
                           oninput="autoBuscar(this.form)"
                           placeholder="🔍 Código OT, Cédula / Doc, cliente, serie o falla..." 
                           class="input-control"
                           autofocus>
                </div>

                <select name="estado" onchange="this.form.submit()" class="select-control">
                    <option value="">Todos los Estados</option>
                    <option value="pendiente" {{ ($estado == 'pendiente') ? 'selected' : '' }}>🟡 Pendiente</option>
                    <option value="en_proceso" {{ ($estado == 'en_proceso') ? 'selected' : '' }}>🔵 En Proceso</option>
                    <option value="finalizado" {{ ($estado == 'finalizado') ? 'selected' : '' }}>🟢 Finalizado</option>
                    <option value="entregado" {{ ($estado == 'entregado') ? 'selected' : '' }}>⚪ Entregado</option>
                    <option value="cancelado" {{ ($estado == 'cancelado') ? 'selected' : '' }}>🔴 Cancelado</option>
                </select>

                <select name="tipo_ubicacion" onchange="this.form.submit()" class="select-control">
                    <option value="">Todas las Ubicaciones</option>
                    <option value="ingresado_al_taller" {{ ($tipoUbicacion == 'ingresado_al_taller') ? 'selected' : '' }}>🏢 En Taller</option>
                    <option value="servicio_en_domicilio" {{ ($tipoUbicacion == 'servicio_en_domicilio') ? 'selected' : '' }}>🏠 Domicilio</option>
                </select>

                @if(!empty($busqueda) || !empty($estado) || !empty($tipoUbicacion))
                    <a href="{{ route('ordenes.index') }}" class="btn btn-outline btn-icon" title="Limpiar filtros">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>

            <a href="{{ route('ordenes.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Nueva Orden</span>
            </a>
        </form>
    </div>

    <!-- Tabla de Órdenes -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código / Fecha</th>
                        <th>Cliente Titular</th>
                        <th>Equipo</th>
                        <th>Modalidad</th>
                        <th>Técnico Asignado</th>
                        <th>Estado</th>
                        <th>Total ($)</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ordenes as $orden)
                        <tr data-href="{{ route('ordenes.show', $orden) }}" class="clickable-row" title="Clic para ver detalles de la orden {{ $orden->codigo_orden }}">
                            <td>
                                <a href="{{ route('ordenes.show', $orden) }}" style="text-decoration: none; color: inherit; display: block;">
                                    <strong style="color: #0284c7; font-size: 14px;">{{ $orden->codigo_orden }}</strong>
                                    <span style="font-size: 11px; color: #64748b; display: block;">
                                        {{ $orden->fecha_ingreso?->format('d/m/Y h:i A') }}
                                    </span>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('clientes.show', $orden->cliente) }}" style="text-decoration: none; color: #0f172a; font-weight: 700; font-size: 13.5px; display: block;">
                                    {{ $orden->cliente?->nombre_completo }}
                                </a>
                                <span style="font-size: 11px; color: #64748b; font-family: monospace;">
                                    {{ $orden->cliente?->identificacion ? 'Doc: ' . $orden->cliente?->identificacion : $orden->cliente?->telefono }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: #334155; font-size: 13px; display: block;">
                                    {{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}
                                </span>
                                <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">
                                    {{ $orden->equipo?->numero_serie ? 'S/N: ' . $orden->equipo?->numero_serie : 'Sin serial' }}
                                </span>
                            </td>
                            <td>
                                @if($orden->tipo_ubicacion === 'servicio_en_domicilio')
                                    <span class="badge badge-purple" style="font-size: 10px;">
                                        <i class="fa-solid fa-house"></i> Domicilio
                                    </span>
                                @else
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd; font-size: 10px;">
                                        <i class="fa-solid fa-shop"></i> Taller
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size: 12.5px; color: #334155; font-weight: 600;">
                                    {{ $orden->tecnico?->nombre_completo ?? 'Sin asignar' }}
                                </span>
                            </td>
                            <td>
                                @if($orden->estado === 'finalizado')
                                    <span class="badge badge-active"><i class="fa-solid fa-circle-check"></i> Finalizado</span>
                                @elseif($orden->estado === 'en_proceso')
                                    <span class="badge badge-process"><i class="fa-solid fa-screwdriver-wrench"></i> En Proceso</span>
                                @elseif($orden->estado === 'entregado')
                                    <span class="badge badge-slate"><i class="fa-solid fa-box-open"></i> Entregado</span>
                                @elseif($orden->estado === 'cancelado')
                                    <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Cancelado</span>
                                @else
                                    <span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <strong style="font-size: 14px; color: #0f172a;">${{ number_format($orden->costo_total, 0, ',', '.') }}</strong>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <!-- Botón Enviar WhatsApp con Reporte Público -->
                                    <a href="{{ $orden->enlace_whatsapp_reporte }}" target="_blank" class="btn btn-emerald btn-icon" title="Enviar reporte por WhatsApp" style="width: 30px; height: 30px; font-size: 13px;">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>

                                    <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline btn-icon" title="Ver Detalles">
                                        <i class="fa-solid fa-eye" style="color: #0284c7;"></i>
                                    </a>

                                    <a href="{{ route('ordenes.edit', $orden) }}" class="btn btn-outline btn-icon" title="Editar / Gestionar">
                                        <i class="fa-solid fa-pen-to-square" style="color: #d97706;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-clipboard-question" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                No se encontraron órdenes de trabajo registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ordenes->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
                {{ $ordenes->links() }}
            </div>
        @endif
    </div>

</div>

<script>
    var debounceTimer;
    function autoBuscar(form) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            form.submit();
        }, 400);
    }
</script>
@endsection
