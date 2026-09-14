@extends('layouts.app')

@section('titulo', 'Equipo de Trabajo & Técnicos')

@section('contenido')
<div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('personal.index') }}" class="search-group" style="flex: 1; max-width: 460px;">
            <div class="search-input-wrapper" style="width: 100%;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" 
                       name="buscar" 
                       value="{{ $busqueda ?? '' }}" 
                       oninput="autoBuscar(this.form)"
                       placeholder="🔍 Nombre, correo, teléfono o rol..." 
                       class="input-control"
                       autofocus>
            </div>
            @if(!empty($busqueda))
                <a href="{{ route('personal.index') }}" class="btn btn-outline btn-icon" title="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>

        <div style="display: flex; align-items: center; gap: 12px;">
            @if($limiteUsuarios)
                <span style="font-size: 12px; font-weight: 800; color: {{ $limiteAlcanzado ? '#dc2626' : '#0369a1' }}; background: {{ $limiteAlcanzado ? '#fee2e2' : '#e0f2fe' }}; padding: 6px 12px; border-radius: 10px;">
                    <i class="fa-solid fa-users"></i> {{ $totalUsuarios }} / {{ $limiteUsuarios }} Cuentas Usadas
                </span>
            @endif

            @if(!$limiteAlcanzado)
                <a href="{{ route('personal.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Nuevo Técnico / Colaborador</span>
                </a>
            @else
                <button class="btn btn-outline" style="cursor: not-allowed; opacity: 0.6;" title="Límite del plan alcanzado">
                    <i class="fa-solid fa-lock"></i> Límite de Plan Alcanzado
                </button>
            @endif
        </div>
    </div>

    <!-- Tabla de Colaboradores del Taller -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre / Colaborador</th>
                        <th>Correo Electrónico (Login)</th>
                        <th>Teléfono</th>
                        <th>Rol en el Taller</th>
                        <th style="text-align: center;">Órdenes Atendidas</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $colaborador)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $colaborador->esAdminTaller() ? '#e0f2fe' : '#ecfdf5' }}; color: {{ $colaborador->esAdminTaller() ? '#0369a1' : '#065f46' }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px;">
                                        {{ strtoupper(substr($colaborador->nombre, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong style="color: #0f172a; font-size: 14px; display: block;">{{ $colaborador->nombre_completo }}</strong>
                                        @if($colaborador->id === auth()->id())
                                            <span style="font-size: 10px; color: #0284c7; font-weight: 800;">(Tú - Sesión Activa)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 12.5px; color: #334155;">{{ $colaborador->email }}</span>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; color: #64748b;">{{ $colaborador->telefono ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($colaborador->esAdminTaller())
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                                        <i class="fa-solid fa-user-shield"></i> Administrador
                                    </span>
                                @elseif($colaborador->esTecnico())
                                    <span class="badge" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                                        <i class="fa-solid fa-screwdriver-wrench"></i> Técnico
                                    </span>
                                @else
                                    <span class="badge badge-slate">{{ ucfirst($colaborador->rol) }}</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-slate" style="font-size: 11px;">
                                    {{ $colaborador->ordenes_asignadas_count }}
                                </span>
                            </td>
                            <td>
                                @if($colaborador->esta_activo)
                                    <span class="badge badge-active">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('personal.edit', $colaborador) }}" class="btn btn-outline btn-icon" title="Editar / Resetear Contraseña">
                                        <i class="fa-solid fa-pen-to-square" style="color: #d97706;"></i>
                                    </a>

                                    @if($colaborador->id !== auth()->id())
                                        <form action="{{ route('personal.destroy', $colaborador) }}" method="POST" class="inline" onsubmit="return confirm('¿Desactivar el acceso a este usuario?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline btn-icon" title="Desactivar">
                                                <i class="fa-solid fa-trash" style="color: #ef4444;"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-users-slash" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                No se encontraron colaboradores que coincidan con la búsqueda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
