@extends('layouts.app')

@section('titulo', 'Gestión de Clientes')

@section('contenido')
<div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('clientes.index') }}" id="formBuscarClientes" class="search-group" style="flex: 1; max-width: 540px;">
            <div class="search-input-wrapper" style="width: 100%;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" 
                       id="inputBuscarClientes"
                       name="buscar" 
                       value="{{ $busqueda ?? '' }}" 
                       oninput="autoBuscar(this.form)"
                       placeholder="🔍 Escribe nombre, Cédula / Documento, teléfono, barrio..." 
                       class="input-control"
                       autofocus>
            </div>
            @if(!empty($busqueda))
                <a href="{{ route('clientes.index') }}" class="btn btn-outline btn-icon" title="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>

        <a href="{{ route('clientes.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-user-plus"></i>
            <span>Nuevo Cliente</span>
        </a>
    </div>

    <!-- Tabla de Clientes -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente / Documento</th>
                        <th>WhatsApp & Teléfono</th>
                        <th>Ubicación (Maps / Waze)</th>
                        <th style="text-align: center;">Equipos</th>
                        <th style="text-align: center;">Órdenes</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clientes as $cliente)
                        <tr>
                            <td>
                                <a href="{{ route('clientes.show', $cliente) }}" style="font-weight: 800; color: #0284c7; text-decoration: none; font-size: 14px; display: block;">
                                    {{ $cliente->nombre_completo }}
                                </a>
                                <span style="font-size: 11px; color: #64748b; font-family: monospace; font-weight: 700;">
                                    {{ $cliente->identificacion ? 'Doc: ' . $cliente->identificacion : 'Sin documento' }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <strong style="color: #334155;">{{ $cliente->telefono }}</strong>
                                    <!-- Botón WhatsApp Directo -->
                                    <a href="{{ $cliente->enlace_whatsapp }}" target="_blank" class="btn btn-emerald btn-icon" title="Abrir chat en WhatsApp" style="width: 28px; height: 28px; font-size: 13px;">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                </div>
                                @if($cliente->email)
                                    <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 2px;">{{ $cliente->email }}</span>
                                @endif
                            </td>
                            <td>
                                <div style="font-size: 12.5px; color: #334155;">
                                    {{ $cliente->direccion }}
                                    @if($cliente->barrio)
                                        <span style="color: #64748b; font-size: 11.5px;">- {{ $cliente->barrio }}</span>
                                    @endif
                                </div>
                                <div style="display: flex; gap: 6px; margin-top: 4px;">
                                    <a href="{{ $cliente->enlace_google_maps }}" target="_blank" style="font-size: 11px; color: #0284c7; text-decoration: none; font-weight: 700;">
                                        <i class="fa-solid fa-map-location-dot"></i> Maps
                                    </a>
                                    <span style="color: #cbd5e1;">•</span>
                                    <a href="{{ $cliente->enlace_waze }}" target="_blank" style="font-size: 11px; color: #0891b2; text-decoration: none; font-weight: 700;">
                                        <i class="fa-brands fa-waze"></i> Waze
                                    </a>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; font-size: 11px;">
                                    {{ $cliente->equipos_count }} {{ Str::plural('Equipo', $cliente->equipos_count) }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd; font-size: 11px;">
                                    {{ $cliente->ordenes_trabajo_count }} {{ Str::plural('Orden', $cliente->ordenes_trabajo_count) }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('ordenes.create', ['cliente_id' => $cliente->id]) }}" class="btn btn-primary" style="padding: 6px 10px; font-size: 11px;" title="Nueva Orden">
                                        + Orden
                                    </a>
                                    <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline btn-icon" title="Ver Perfil">
                                        <i class="fa-solid fa-eye" style="color: #0284c7;"></i>
                                    </a>
                                    <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-outline btn-icon" title="Editar">
                                        <i class="fa-solid fa-pen-to-square" style="color: #d97706;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-user-slash" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                No se encontraron clientes que coincidan con "{{ $busqueda }}".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
                {{ $clientes->links() }}
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
