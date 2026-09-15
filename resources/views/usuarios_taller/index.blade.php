@extends('layouts.app')

@section('titulo', 'Equipo de Trabajo & Seguridad de Accesos')

@section('contenido')
<div>

    <!-- Selector de Pestañas: Equipo vs Bitácora de Accesos -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid var(--border, #e2e8f0); padding-bottom: 12px; flex-wrap: wrap; justify-content: space-between; align-items: center;">
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('personal.index', ['tab' => 'equipo']) }}" 
               class="btn {{ $tab === 'equipo' ? 'btn-primary' : 'btn-outline' }}"
               style="border-radius: 12px; font-size: 13.5px; font-weight: 700; padding: 9px 18px;">
                <i class="fa-solid fa-users"></i>
                <span>Equipo de Trabajo</span>
                <span style="background: rgba(255,255,255,0.25); padding: 2px 8px; border-radius: 20px; font-size: 11px; margin-left: 4px;">
                    {{ $usuarios->count() }}
                </span>
            </a>

            <a href="{{ route('personal.index', ['tab' => 'seguridad']) }}" 
               class="btn {{ $tab === 'seguridad' ? 'btn-primary' : 'btn-outline' }}"
               style="border-radius: 12px; font-size: 13.5px; font-weight: 700; padding: 9px 18px;">
                <i class="fa-solid fa-shield-halved"></i>
                <span>Historial de Accesos & Seguridad</span>
                <span style="background: rgba(255,255,255,0.25); padding: 2px 8px; border-radius: 20px; font-size: 11px; margin-left: 4px;">
                    {{ $registrosAcceso->total() }}
                </span>
            </a>
        </div>

        @if($tab === 'equipo')
            <div style="display: flex; align-items: center; gap: 12px;">
                @if($limiteUsuarios)
                    <span style="font-size: 12px; font-weight: 800; color: {{ $limiteAlcanzado ? '#dc2626' : '#0369a1' }}; background: {{ $limiteAlcanzado ? '#fee2e2' : '#e0f2fe' }}; padding: 6px 12px; border-radius: 10px;">
                        <i class="fa-solid fa-users"></i> {{ $totalUsuarios }} / {{ $limiteUsuarios }} Cuentas
                    </span>
                @endif

                @if(!$limiteAlcanzado)
                    <a href="{{ route('personal.create') }}" class="btn btn-primary" style="border-radius: 12px; font-size: 13.5px;">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Nuevo Técnico / Colaborador</span>
                    </a>
                @else
                    <button class="btn btn-outline" style="cursor: not-allowed; opacity: 0.6; border-radius: 12px;" title="Límite del plan alcanzado">
                        <i class="fa-solid fa-lock"></i> Límite de Plan Alcanzado
                    </button>
                @endif
            </div>
        @endif
    </div>

    @if($tab === 'equipo')
        <!-- Barra de Búsqueda de Personal -->
        <div class="filter-bar" style="margin-bottom: 16px;">
            <form method="GET" action="{{ route('personal.index') }}" class="search-group" style="flex: 1; max-width: 460px;">
                <input type="hidden" name="tab" value="equipo">
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
                    <a href="{{ route('personal.index', ['tab' => 'equipo']) }}" class="btn btn-outline btn-icon" title="Limpiar búsqueda">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabla de Colaboradores del Taller -->
        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nombre / Colaborador</th>
                            <th>Correo Electrónico</th>
                            <th>Teléfono</th>
                            <th>Rol en el Taller</th>
                            <th style="text-align: center;">Órdenes</th>
                            <th>Sesión Actual</th>
                            <th>Estado Cuenta</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $colaborador)
                            <tr data-href="{{ route('personal.edit', $colaborador) }}" class="clickable-row" title="Clic para ver / editar detalles de {{ $colaborador->nombre_completo }}">
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 38px; height: 38px; border-radius: 10px; background: {{ $colaborador->esAdminTaller() ? '#e0f2fe' : '#ecfdf5' }}; color: {{ $colaborador->esAdminTaller() ? '#0369a1' : '#065f46' }}; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13.5px; flex-shrink: 0;">
                                            {{ strtoupper(substr($colaborador->nombre, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong style="color: var(--text-main, #0f172a); font-size: 14px; display: block;">{{ $colaborador->nombre_completo }}</strong>
                                            @if($colaborador->id === auth()->id())
                                                <span style="font-size: 11px; color: #0284c7; font-weight: 800;">(Tu usuario activo)</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-family: monospace; font-size: 12.5px; color: var(--text-main, #334155);">{{ $colaborador->email }}</span>
                                </td>
                                <td>
                                    <span style="font-size: 12.5px; color: var(--text-muted, #64748b);">{{ $colaborador->telefono ?? 'N/A' }}</span>
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
                                    <span class="badge badge-slate" style="font-size: 11.5px;">
                                        {{ $colaborador->ordenes_asignadas_count }}
                                    </span>
                                </td>
                                <td>
                                    @if($colaborador->estaEnLinea())
                                        <span class="badge" style="background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 8px #10b981;"></span>
                                            En Línea
                                        </span>
                                    @else
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                                            <span style="width: 7px; height: 7px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span>
                                            Desconectado
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($colaborador->esta_activo)
                                        <span class="badge badge-active">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td style="text-align: right;" onclick="event.stopPropagation();">
                                    <div style="display: inline-flex; gap: 6px;">
                                        @if($colaborador->estaEnLinea() && $colaborador->id !== auth()->id())
                                            <form action="{{ route('personal.desconectar', $colaborador) }}" method="POST" class="inline" onsubmit="return confirm('¿Deseas cerrar forzosamente la sesión activa de {{ $colaborador->nombre_completo }}?');">
                                                @csrf
                                                <button type="submit" class="btn btn-outline btn-icon" title="Cerrar sesión activa remota" style="color: #ea580c; border-color: #fed7aa; background: #fff7ed;">
                                                    <i class="fa-solid fa-power-off"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('personal.edit', $colaborador) }}" class="btn btn-outline btn-icon" title="Editar / Modificar Contraseña">
                                            <i class="fa-solid fa-pen-to-square" style="color: #d97706;"></i>
                                        </a>

                                        @if($colaborador->id !== auth()->id())
                                            <form action="{{ route('personal.destroy', $colaborador) }}" method="POST" class="inline" onsubmit="return confirm('¿Desactivar el acceso a este usuario?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline btn-icon" title="Desactivar cuenta">
                                                    <i class="fa-solid fa-trash" style="color: #ef4444;"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                                    <i class="fa-solid fa-users-slash" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                    No se encontraron colaboradores que coincidan con la búsqueda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <!-- Pestaña de Bitácora de Accesos & Seguridad -->
        <div class="table-card">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border, #e2e8f0); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--text-main, #0f172a); margin: 0 0 4px 0;">
                        <i class="fa-solid fa-shield-halved" style="color: #0284c7; margin-right: 6px;"></i>
                        Registro de Auditoría de Sesiones y Accesos
                    </h3>
                    <p style="font-size: 12.5px; color: var(--text-muted, #64748b); margin: 0;">
                        Monitoreo estricto de accesos con regla de sesión única (1 usuario = 1 sesión), direcciones IP, geolocalización y caducidad de 15 minutos.
                    </p>
                </div>
                <div>
                    <span class="badge badge-active" style="padding: 6px 12px; font-size: 11.5px;">
                        <i class="fa-solid fa-clock"></i> Auto-cierre por inactividad: 15 min
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Usuario / Correo</th>
                            <th>Fecha & Hora Ingreso</th>
                            <th>Ubicación & IP</th>
                            <th>Dispositivo & Navegador</th>
                            <th>Estado de Acceso</th>
                            <th>Duración Sesión</th>
                            <th>Fecha Salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrosAcceso as $registro)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0369a1; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; flex-shrink: 0;">
                                            {{ strtoupper(substr($registro->usuario?->nombre ?? $registro->email_ingresado, 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong style="color: var(--text-main, #0f172a); font-size: 13.5px; display: block;">
                                                {{ $registro->usuario?->nombre_completo ?? 'Intento Desconocido' }}
                                            </strong>
                                            <span style="font-size: 11.5px; color: var(--text-muted, #64748b); font-family: monospace;">
                                                {{ $registro->email_ingresado }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 12.5px; color: var(--text-main, #334155); font-weight: 600;">
                                        {{ $registro->fecha_ingreso ? $registro->fecha_ingreso->format('d/m/Y h:i A') : 'N/A' }}
                                    </div>
                                    <div style="font-size: 11px; color: var(--text-muted, #94a3b8);">
                                        {{ $registro->fecha_ingreso ? $registro->fecha_ingreso->diffForHumans() : '' }}
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <i class="fa-solid fa-location-dot" style="color: #ef4444; font-size: 12px;"></i>
                                        <span style="font-size: 12.5px; font-weight: 600; color: var(--text-main, #334155);">
                                            {{ $registro->ciudad ? ($registro->ciudad . ', ' . $registro->pais) : 'Red Local / Privada' }}
                                        </span>
                                    </div>
                                    <span style="font-family: monospace; font-size: 11px; color: var(--text-muted, #64748b); display: block; margin-left: 18px;">
                                        IP: {{ $registro->ip_address }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 12.5px; color: var(--text-main, #334155); font-weight: 600;">
                                        @if(str_contains(strtolower($registro->sistema_operativo ?? ''), 'windows'))
                                            <i class="fa-brands fa-windows" style="color: #0284c7; margin-right: 4px;"></i>
                                        @elseif(str_contains(strtolower($registro->sistema_operativo ?? ''), 'mac') || str_contains(strtolower($registro->sistema_operativo ?? ''), 'ios'))
                                            <i class="fa-brands fa-apple" style="color: #64748b; margin-right: 4px;"></i>
                                        @elseif(str_contains(strtolower($registro->sistema_operativo ?? ''), 'android'))
                                            <i class="fa-brands fa-android" style="color: #10b981; margin-right: 4px;"></i>
                                        @elseif(str_contains(strtolower($registro->sistema_operativo ?? ''), 'linux'))
                                            <i class="fa-brands fa-linux" style="color: #ea580c; margin-right: 4px;"></i>
                                        @else
                                            <i class="fa-solid fa-desktop" style="color: #64748b; margin-right: 4px;"></i>
                                        @endif
                                        {{ $registro->sistema_operativo ?? 'SO Desconocido' }}
                                    </div>
                                    <div style="font-size: 11.5px; color: var(--text-muted, #64748b);">
                                        <i class="fa-solid fa-globe" style="font-size: 10px; margin-right: 3px;"></i>
                                        {{ $registro->navegador ?? 'Web' }} • {{ $registro->dispositivo ?? 'PC' }}
                                    </div>
                                </td>
                                <td>
                                    @switch($registro->estado)
                                        @case('exitoso')
                                            <span class="badge badge-active" style="font-weight: 700;">
                                                <i class="fa-solid fa-circle-check"></i> Acceso Exitoso
                                            </span>
                                            @break
                                        @case('fallido')
                                            <span class="badge badge-danger" style="font-weight: 700;" title="{{ $registro->motivo_cierre }}">
                                                <i class="fa-solid fa-circle-xmark"></i> Fallido
                                            </span>
                                            @break
                                        @case('cerrado_por_otra_sesion')
                                            <span class="badge" style="background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; font-weight: 700;">
                                                <i class="fa-solid fa-arrow-right-arrow-left"></i> Otra Sesión Iniciada
                                            </span>
                                            @break
                                        @case('inactividad')
                                            <span class="badge" style="background: #fefce8; color: #a16207; border: 1px solid #fef08a; font-weight: 700;">
                                                <i class="fa-solid fa-hourglass-end"></i> Inactividad (15 min)
                                            </span>
                                            @break
                                        @case('forzado_admin')
                                            <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 700;">
                                                <i class="fa-solid fa-user-slash"></i> Desconectado por Admin
                                            </span>
                                            @break
                                        @case('logout')
                                            <span class="badge badge-slate" style="font-weight: 700;">
                                                <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout Voluntario
                                            </span>
                                            @break
                                        @default
                                            <span class="badge badge-slate">{{ ucfirst($registro->estado) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <span style="font-size: 12px; font-weight: 700; color: var(--text-main, #334155);">
                                        {{ $registro->obtenerDuracionFormateada() }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 12px; color: var(--text-muted, #64748b);">
                                        {{ $registro->fecha_cierre ? $registro->fecha_cierre->format('d/m/Y h:i A') : 'En curso / Activa' }}
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted, #94a3b8);">
                                    <i class="fa-solid fa-shield-cat" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                    Aún no hay registros de accesos registrados en la bitácora de seguridad.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($registrosAcceso->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid var(--border, #e2e8f0);">
                    {{ $registrosAcceso->links() }}
                </div>
            @endif
        </div>
    @endif

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

