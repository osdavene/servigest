@extends('layouts.app')

@section('titulo', 'Super Administradores SaaS')

@section('contenido')
<div>

    <!-- Encabezado y Barra de Filtros -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('superadmin.usuarios.index') }}" class="search-group" style="flex: 1; max-width: 480px;">
            <div class="search-input-wrapper" style="width: 100%;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" 
                       name="buscar" 
                       value="{{ $busqueda ?? '' }}" 
                       oninput="autoBuscar(this.form)"
                       placeholder="🔍 Nombre, correo o teléfono de SuperAdmin..." 
                       class="input-control"
                       autofocus>
            </div>
            @if(!empty($busqueda))
                <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline btn-icon" title="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>

        <a href="{{ route('superadmin.usuarios.create') }}" class="btn btn-amber">
            <i class="fa-solid fa-user-plus"></i>
            <span>Nuevo Super Administrador</span>
        </a>
    </div>

    <!-- Tabla de Super Administradores -->
    <div class="table-card">
        <div class="card-header" style="padding: 18px 24px; margin-bottom: 0;">
            <div>
                <h3 style="display: flex; align-items: center; gap: 8px; font-size: 16px;">
                    <i class="fa-solid fa-crown" style="color: #d97706;"></i>
                    <span>Cuentas Maestras del SaaS</span>
                </h3>
                <p style="font-size: 12.5px;">Usuarios con permisos absolutos de administración, suscripciones y configuración de infraestructura.</p>
            </div>
            <div>
                <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #b45309; border: 1px solid #fde68a; font-weight: 800;">
                    <i class="fa-solid fa-shield-halved"></i> {{ $superadmins->count() }} {{ Str::plural('Cuenta', $superadmins->count()) }}
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Super Administrador</th>
                        <th>Correo Maestro (Login)</th>
                        <th>Teléfono</th>
                        <th>Sesión en Vivo</th>
                        <th>Último Ingreso</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($superadmins as $admin)
                        <tr data-href="{{ route('superadmin.usuarios.edit', $admin) }}" class="clickable-row" title="Clic para editar o cambiar contraseña de {{ $admin->nombre_completo }}">
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 38px; height: 38px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 14px; box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3); flex-shrink: 0;">
                                        {{ strtoupper(substr($admin->nombre, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong style="color: var(--text-main, #0f172a); font-size: 14px; display: block;">
                                            {{ $admin->nombre_completo }}
                                        </strong>
                                        @if($admin->id === auth()->id())
                                            <span style="font-size: 10.5px; color: #d97706; font-weight: 800;">(Tu Sesión Actual)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 12.5px; color: var(--text-main, #334155);">{{ $admin->email }}</span>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; color: var(--text-muted, #64748b);">{{ $admin->telefono ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($admin->estaEnLinea())
                                    <span class="badge" style="background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 8px #10b981;"></span>
                                        En Línea
                                    </span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-weight: 600;">Desconectado</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->ultimo_login_at)
                                    <div style="font-size: 12px; color: var(--text-main, #334155); font-weight: 600;">
                                        {{ $admin->ultimo_login_at->format('d/m/Y h:i A') }}
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-muted, #94a3b8);">
                                        IP: {{ $admin->ultimo_login_ip ?? 'Local' }}
                                    </div>
                                @else
                                    <span style="font-size: 11px; color: var(--text-muted, #94a3b8);">Sin registro previo</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->esta_activo)
                                    <span class="badge badge-active">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                            <td style="text-align: right;" onclick="event.stopPropagation();">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('superadmin.usuarios.edit', $admin) }}" class="btn btn-outline btn-icon" title="Editar / Cambiar Contraseña">
                                        <i class="fa-solid fa-key" style="color: #d97706;"></i>
                                    </a>

                                    @if($admin->id !== auth()->id())
                                        <form action="{{ route('superadmin.usuarios.destroy', $admin) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta cuenta de Super Administrador?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline btn-icon" title="Eliminar cuenta">
                                                <i class="fa-solid fa-trash" style="color: #ef4444;"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                                <i class="fa-solid fa-user-slash" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                No se encontraron cuentas de Super Administrador con esa búsqueda.
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
