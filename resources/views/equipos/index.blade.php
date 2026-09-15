@extends('layouts.app')

@section('titulo', 'Equipos & Dispositivos')

@section('contenido')
<div>

    <!-- Filtros y Búsqueda -->
    <div class="card" style="padding: 18px 24px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('equipos.index') }}" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; flex-wrap: wrap;">
                <div class="search-input-wrapper" style="min-width: 280px; flex: 1;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" 
                           name="buscar" 
                           value="{{ $busqueda ?? '' }}" 
                           oninput="autoBuscar(this.form)"
                           placeholder="🔍 Marca, modelo, serial, Cédula / Doc o cliente..." 
                           class="input-control"
                           autofocus>
                </div>

                <select name="categoria_id" onchange="this.form.submit()" class="select-control">
                    <option value="">Todas las Categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" {{ ($categoriaId == $cat->id) ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                    @endforeach
                </select>

                <label style="display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: #b45309; background: #fffbeb; padding: 8px 12px; border-radius: 10px; border: 1px solid #fde68a; cursor: pointer;">
                    <input type="checkbox" name="alerta_mantenimiento" value="1" onchange="this.form.submit()" {{ $soloMantenimiento ? 'checked' : '' }} style="accent-color: #d97706;">
                    <span>🚨 Solo Próximos / Vencidos</span>
                </label>

                @if(!empty($busqueda) || !empty($categoriaId) || !empty($soloMantenimiento))
                    <a href="{{ route('equipos.index') }}" class="btn btn-outline btn-icon" title="Limpiar filtros">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>

            <a href="{{ route('equipos.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                <span>Registrar Equipo</span>
            </a>
        </form>
    </div>

    <!-- Tabla de Equipos con Enlace a Hoja de Vida -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Equipo / Dispositivo</th>
                        <th>Categoría</th>
                        <th>Cliente Titular</th>
                        <th>N° Serie</th>
                        <th>Mantenimiento Sugerido</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipos as $equipo)
                        <tr data-href="{{ route('equipos.show', $equipo) }}" class="clickable-row" title="Clic para ver hoja de vida e historial de {{ $equipo->marca }} {{ $equipo->modelo }}">
                            <td>
                                <a href="{{ route('equipos.show', $equipo) }}" style="text-decoration: none; color: inherit; display: block;">
                                    <strong style="color: #0284c7; font-size: 14px;">{{ $equipo->marca }} {{ $equipo->modelo }}</strong>
                                    <span style="font-size: 11px; color: #64748b; display: block;">
                                        <i class="fa-solid fa-timeline"></i> {{ $equipo->ordenes_trabajo_count }} {{ Str::plural('servicio', $equipo->ordenes_trabajo_count) }} en historial
                                    </span>
                                </a>
                            </td>
                            <td>
                                <span class="badge" style="background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe;">
                                    {{ $equipo->categoria?->nombre ?? 'General' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('clientes.show', $equipo->cliente) }}" style="text-decoration: none; color: #334155; font-weight: 700; font-size: 13.5px; display: block;">
                                    {{ $equipo->cliente?->nombre_completo }}
                                </a>
                                <span style="font-size: 11px; color: #64748b; font-family: monospace;">
                                    {{ $equipo->cliente?->identificacion ? 'Doc: ' . $equipo->cliente?->identificacion : $equipo->cliente?->telefono }}
                                </span>
                            </td>
                            <td style="font-family: monospace; font-size: 12px; color: #64748b;">
                                {{ $equipo->numero_serie ?? 'N/A' }}
                            </td>
                            <td>
                                @if($equipo->fecha_proximo_mantenimiento)
                                    @if($equipo->requiere_mantenimiento)
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-triangle-exclamation"></i> ¡Vencido {{ \Carbon\Carbon::parse($equipo->fecha_proximo_mantenimiento)->format('d/m/Y') }}!
                                        </span>
                                    @else
                                        <span class="badge badge-pending">
                                            <i class="fa-regular fa-calendar-check"></i> {{ \Carbon\Carbon::parse($equipo->fecha_proximo_mantenimiento)->format('d/m/Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span style="font-size: 12px; color: #94a3b8;">No programado</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('equipos.show', $equipo) }}" class="btn btn-outline" style="font-size: 11px; padding: 6px 10px; color: #0284c7;" title="Ver Hoja de Vida e Historial">
                                        <i class="fa-solid fa-clipboard-list"></i> Hoja de Vida
                                    </a>
                                    <a href="{{ route('ordenes.create', ['equipo_id' => $equipo->id, 'cliente_id' => $equipo->cliente_id]) }}" class="btn btn-primary" style="padding: 6px 10px; font-size: 11px;" title="Nueva Orden">
                                        + Orden
                                    </a>
                                    <a href="{{ route('equipos.edit', $equipo) }}" class="btn btn-outline btn-icon" title="Editar">
                                        <i class="fa-solid fa-pen-to-square" style="color: #d97706;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-laptop-medical" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                No se encontraron equipos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($equipos->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
                {{ $equipos->links() }}
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
