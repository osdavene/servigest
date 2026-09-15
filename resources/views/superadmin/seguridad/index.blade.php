@extends('layouts.app')

@section('titulo', 'Bitácora Global de Accesos & Seguridad SaaS')

@section('contenido')
<div>

    <!-- Header y Descripción -->
    <div style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
            <div style="width: 40px; height: 40px; border-radius: 12px; background: linear-gradient(135deg, #d97706, #b45309); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.35);">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h2 style="font-size: 20px; font-weight: 900; color: var(--text-main, #0f172a); margin: 0;">Centro Global de Seguridad y Auditoría de Accesos</h2>
                <p style="font-size: 13px; color: var(--text-muted, #64748b); margin: 0;">Monitoreo centralizado de sesiones activas, regla de usuario único, intentos fallidos y geolocalización IP en toda la plataforma SaaS.</p>
            </div>
        </div>
    </div>

    <!-- Grid de Métricas de Seguridad Globales -->
    <div class="grid-kpi">
        
        <a href="{{ route('superadmin.seguridad.index', ['estado' => 'exitoso']) }}" class="kpi-card" title="Filtrar Accesos Exitosos">
            <div class="kpi-icon" style="background: #ecfdf5; color: #10b981;">
                <i class="fa-solid fa-right-to-bracket"></i>
            </div>
            <div>
                <div class="kpi-title">Logins Hoy</div>
                <div class="kpi-num" style="color: #10b981;">{{ $totalLoginsHoy }}</div>
            </div>
        </a>

        <div class="kpi-card" style="border-color: #a7f3d0; background: linear-gradient(180deg, var(--card-bg) 0%, rgba(16, 185, 129, 0.05) 100%);">
            <div class="kpi-icon" style="background: #10b981; color: #ffffff; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="kpi-title">Usuarios En Línea</div>
                <div class="kpi-num" style="color: #047857; display: flex; align-items: center; gap: 8px;">
                    {{ $sesionesActivas }}
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; box-shadow: 0 0 10px #10b981;"></span>
                </div>
            </div>
        </div>

        <a href="{{ route('superadmin.seguridad.index', ['estado' => 'fallido']) }}" class="kpi-card" title="Filtrar Intentos Fallidos">
            <div class="kpi-icon" style="background: #fee2e2; color: #ef4444;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="kpi-title">Intentos Fallidos Hoy</div>
                <div class="kpi-num" style="color: #dc2626;">{{ $intentosFallidosHoy }}</div>
            </div>
        </a>

        <a href="{{ route('superadmin.talleres.index') }}" class="kpi-card" title="Ver Talleres Conectados">
            <div class="kpi-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fa-solid fa-shop"></i>
            </div>
            <div>
                <div class="kpi-title">Talleres Activos Hoy</div>
                <div class="kpi-num" style="color: #d97706;">{{ $talleresConectadosHoy }}</div>
            </div>
        </a>

    </div>

    <!-- Barra de Filtros de Auditoría -->
    <div class="card" style="padding: 16px 20px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('superadmin.seguridad.index') }}" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; flex-wrap: wrap;">
                <div class="search-input-wrapper" style="min-width: 280px; flex: 1;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" 
                           name="buscar" 
                           value="{{ $busqueda ?? '' }}" 
                           oninput="autoBuscar(this.form)"
                           placeholder="🔍 Correo, IP, ciudad, usuario o taller..." 
                           class="input-control"
                           autofocus>
                </div>

                <select name="taller_id" onchange="this.form.submit()" class="select-control" style="min-width: 180px;">
                    <option value="">🏢 Todos los Talleres</option>
                    <option value="superadmin" {{ ($tallerId === 'superadmin') ? 'selected' : '' }}>👑 Consola SuperAdmin</option>
                    @foreach($talleres as $t)
                        <option value="{{ $t->id }}" {{ ($tallerId == $t->id) ? 'selected' : '' }}>
                            {{ $t->nombre_comercial }}
                        </option>
                    @endforeach
                </select>

                <select name="estado" onchange="this.form.submit()" class="select-control" style="min-width: 170px;">
                    <option value="">⚡ Todos los Eventos</option>
                    <option value="exitoso" {{ ($estado === 'exitoso') ? 'selected' : '' }}>🟢 Acceso Exitoso</option>
                    <option value="fallido" {{ ($estado === 'fallido') ? 'selected' : '' }}>🔴 Login Fallido</option>
                    <option value="cerrado_por_otra_sesion" {{ ($estado === 'cerrado_por_otra_sesion') ? 'selected' : '' }}>🟠 Otra Sesión Iniciada</option>
                    <option value="inactividad" {{ ($estado === 'inactividad') ? 'selected' : '' }}>🟡 Inactividad (15 min)</option>
                    <option value="forzado_admin" {{ ($estado === 'forzado_admin') ? 'selected' : '' }}>⛔ Desconectado por Admin</option>
                    <option value="logout" {{ ($estado === 'logout') ? 'selected' : '' }}>⚪ Logout Voluntario</option>
                </select>

                @if(!empty($busqueda) || !empty($tallerId) || !empty($estado))
                    <a href="{{ route('superadmin.seguridad.index') }}" class="btn btn-outline btn-icon" title="Limpiar filtros">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
            </div>

            <div>
                <span class="badge badge-active" style="padding: 7px 14px; font-size: 11.5px; border-radius: 10px;">
                    <i class="fa-solid fa-lock"></i> Política Sesión Única Activa
                </span>
            </div>
        </form>
    </div>

    <!-- Tabla Global de Auditoría -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Usuario / Cuenta</th>
                        <th>Empresa / Taller</th>
                        <th>Fecha & Hora Ingreso</th>
                        <th>Ubicación & IP</th>
                        <th>Dispositivo & Navegador</th>
                        <th>Evento / Estado</th>
                        <th>Duración</th>
                        <th>Fecha Cierre</th>
                        <th style="text-align: right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrosAcceso as $registro)
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 34px; height: 34px; border-radius: 10px; background: {{ $registro->usuario?->esSuperAdmin() ? '#fef3c7' : '#e0f2fe' }}; color: {{ $registro->usuario?->esSuperAdmin() ? '#b45309' : '#0369a1' }}; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex-shrink: 0;">
                                        @if($registro->usuario?->esSuperAdmin())
                                            <i class="fa-solid fa-crown" style="font-size: 12px;"></i>
                                        @else
                                            {{ strtoupper(substr($registro->usuario?->nombre ?? $registro->email_ingresado, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <strong style="color: var(--text-main, #0f172a); font-size: 13.5px; display: block;">
                                            {{ $registro->usuario?->nombre_completo ?? 'Intento Anónimo' }}
                                        </strong>
                                        <span style="font-size: 11.5px; color: var(--text-muted, #64748b); font-family: monospace;">
                                            {{ $registro->email_ingresado }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($registro->taller)
                                    <span class="badge badge-slate" style="font-weight: 700;">
                                        <i class="fa-solid fa-shop" style="color: #0284c7; margin-right: 4px;"></i>
                                        {{ $registro->taller->nombre_comercial }}
                                    </span>
                                @elseif($registro->usuario?->esSuperAdmin())
                                    <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-weight: 700;">
                                        <i class="fa-solid fa-shield-halved" style="color: #d97706; margin-right: 4px;"></i>
                                        Consola Master SaaS
                                    </span>
                                @else
                                    <span style="font-size: 11.5px; color: var(--text-muted, #94a3b8);">N/A</span>
                                @endif
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
                                            <i class="fa-solid fa-arrow-right-arrow-left"></i> Sesión Reemplazada
                                        </span>
                                        @break
                                    @case('inactividad')
                                        <span class="badge" style="background: #fefce8; color: #a16207; border: 1px solid #fef08a; font-weight: 700;">
                                            <i class="fa-solid fa-hourglass-end"></i> Inactividad (15 min)
                                        </span>
                                        @break
                                    @case('forzado_admin')
                                    @case('cerrado_por_administrador')
                                        <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-weight: 700;">
                                            <i class="fa-solid fa-user-slash"></i> Desconectado por Admin
                                        </span>
                                        @break
                                    @case('logout')
                                        <span class="badge badge-slate" style="font-weight: 700;">
                                            <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                                        </span>
                                        @break
                                    @default
                                        <span class="badge badge-slate">{{ ucfirst($registro->estado) }}</span>
                                @endswitch
                            </td>
                            <td>
                                @php
                                    $duracionTexto = 'En curso / Activa';
                                    if ($registro->fecha_cierre && $registro->fecha_ingreso) {
                                        $mins = $registro->fecha_ingreso->diffInMinutes($registro->fecha_cierre);
                                        if ($mins < 1) {
                                            $duracionTexto = '< 1 min';
                                        } elseif ($mins < 60) {
                                            $duracionTexto = "{$mins} min";
                                        } else {
                                            $duracionTexto = intdiv($mins, 60) . 'h ' . ($mins % 60) . 'm';
                                        }
                                    }
                                @endphp
                                <span style="font-size: 12px; font-weight: 700; color: var(--text-main, #334155);">
                                    {{ $duracionTexto }}
                                </span>
                            </td>
                            <td>
                                <div style="font-size: 12px; color: var(--text-muted, #64748b);">
                                    {{ $registro->fecha_cierre ? $registro->fecha_cierre->format('d/m/Y h:i A') : 'En curso / Activa' }}
                                </div>
                            </td>
                            <td style="text-align: right;">
                                @if($registro->usuario && $registro->usuario->estaEnLinea() && $registro->usuario->id !== auth()->id())
                                    <form action="{{ route('superadmin.seguridad.desconectar', $registro->usuario) }}" method="POST" class="inline" onsubmit="return confirm('¿Desconectar y cerrar la sesión activa del usuario {{ $registro->usuario->nombre_completo }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-icon" title="Cerrar sesión activa de este usuario" style="color: #ea580c; border-color: #fed7aa; background: #fff7ed;">
                                            <i class="fa-solid fa-power-off"></i>
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 11px; color: var(--text-muted, #94a3b8);">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 48px; color: var(--text-muted, #94a3b8);">
                                <i class="fa-solid fa-shield-cat" style="font-size: 36px; display: block; margin-bottom: 10px;"></i>
                                No se encontraron registros de accesos con los filtros aplicados.
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
