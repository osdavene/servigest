@extends('layouts.app')

@section('titulo', 'Detalles de Empresa: ' . $taller->nombre_comercial)

@section('contenido')
<div>

    <!-- Encabezado Principal de la Empresa -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            @if($taller->url_logo)
                <img src="{{ $taller->url_logo }}" alt="Logo" style="width: 56px; height: 56px; border-radius: 16px; object-fit: cover; border: 1.5px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
            @else
                <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 900; box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);">
                    {{ strtoupper(substr($taller->nombre_comercial, 0, 1)) }}
                </div>
            @endif

            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="font-size: 22px; font-weight: 900; color: var(--text-main, #0f172a); margin: 0;">{{ $taller->nombre_comercial }}</h2>
                    @if($taller->estado_suscripcion === 'activo')
                        <span class="badge badge-active"><i class="fa-solid fa-circle-check"></i> Activo</span>
                    @elseif($taller->estado_suscripcion === 'periodo_prueba')
                        <span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Prueba Gratuita</span>
                    @else
                        <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Suspendido</span>
                    @endif
                </div>
                <p style="font-size: 13px; color: var(--text-muted, #64748b); margin: 4px 0 0 0;">
                    {{ $taller->identificacion_fiscal ? 'NIT: ' . $taller->identificacion_fiscal : 'Sin NIT' }} • {{ $taller->ciudad }} • {{ $taller->email }} • {{ $taller->telefono }}
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <a href="{{ route('superadmin.respaldos.taller', $taller) }}" class="btn btn-outline" style="font-size: 12.5px;">
                <i class="fa-solid fa-file-zipper" style="color: #f59e0b;"></i>
                <span>Descargar Backup .ZIP</span>
            </a>

            <a href="{{ route('superadmin.talleres.edit', $taller) }}" class="btn btn-primary" style="font-size: 12.5px;">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Editar Empresa</span>
            </a>

            <a href="{{ route('superadmin.talleres.index') }}" class="btn btn-outline" style="font-size: 12.5px;">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Volver</span>
            </a>
        </div>
    </div>

    <!-- Grid de Métricas del Taller -->
    <div class="grid-kpi" style="margin-bottom: 24px;">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: #eff6ff; color: #2563eb;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <div class="kpi-title">Usuarios / Personal</div>
                <div class="kpi-num">{{ $taller->usuarios->count() }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: #eef2ff; color: #4f46e5;">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <div>
                <div class="kpi-title">Clientes Registrados</div>
                <div class="kpi-num">{{ $taller->clientes_count ?? $taller->clientes->count() }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: #fdf4ff; color: #a855f7;">
                <i class="fa-solid fa-laptop-medical"></i>
            </div>
            <div>
                <div class="kpi-title">Equipos / Inventario</div>
                <div class="kpi-num">{{ $taller->equipos_count ?? $taller->equipos()->count() }}</div>
            </div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: #ecfdf5; color: #10b981;">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div>
                <div class="kpi-title">Órdenes Realizadas</div>
                <div class="kpi-num" style="color: #10b981;">{{ $taller->ordenes_trabajo_count ?? $taller->ordenesTrabajo()->count() }}</div>
            </div>
        </div>
    </div>

    <!-- Panel de Suscripción y Licencia -->
    <div class="card" style="border-color: #fde68a; margin-bottom: 24px; padding: 22px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 18px;">
            <div>
                <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: 10.5px; font-weight: 800; margin-bottom: 6px;">
                    <i class="fa-solid fa-award"></i> Plan Actual: {{ $taller->planLicencia?->nombre ?? 'Plan Estándar' }}
                </span>
                <h3 style="font-size: 16px; font-weight: 900; color: var(--text-main, #0f172a); margin: 4px 0 0 0;">
                    Estado de Licencia: Vence el {{ $taller->fecha_vencimiento_suscripcion ? $taller->fecha_vencimiento_suscripcion->format('d/m/Y') : 'Sin fecha' }}
                    @if($taller->dias_restantes_licencia <= 7)
                        <span class="badge badge-danger" style="margin-left: 8px;">¡{{ $taller->dias_restantes_licencia }} días restantes!</span>
                    @else
                        <span class="badge badge-active" style="margin-left: 8px;">{{ $taller->dias_restantes_licencia }} días restantes</span>
                    @endif
                </h3>
            </div>

            <div style="display: flex; align-items: center; gap: 8px;">
                <form action="{{ route('superadmin.talleres.toggle-estado', $taller) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn btn-outline" style="font-size: 12px; color: {{ $taller->estado_suscripcion === 'activo' ? '#dc2626' : '#16a34a' }};">
                        <i class="fa-solid {{ $taller->estado_suscripcion === 'activo' ? 'fa-ban' : 'fa-check' }}"></i>
                        <span>{{ $taller->estado_suscripcion === 'activo' ? 'Suspender Acceso' : 'Reactivar Taller' }}</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Botones de Extensión Rápida -->
        <div style="padding: 16px; background: var(--bg-page, #f8fafc); border-radius: 14px; border: 1px solid var(--border);">
            <strong style="font-size: 12.5px; color: var(--text-main, #0f172a); display: block; margin-bottom: 10px;">
                <i class="fa-solid fa-bolt" style="color: #d97706; margin-right: 4px;"></i> Extender Licencia en 1-Clic:
            </strong>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <form action="{{ route('superadmin.talleres.extender-licencia', $taller) }}" method="POST">
                    @csrf
                    <input type="hidden" name="dias" value="30">
                    <button type="submit" class="btn btn-outline" style="font-size: 12px; padding: 7px 14px; background: #ffffff;">
                        <i class="fa-solid fa-plus" style="color: #0284c7;"></i> +30 Días (1 Mes)
                    </button>
                </form>

                <form action="{{ route('superadmin.talleres.extender-licencia', $taller) }}" method="POST">
                    @csrf
                    <input type="hidden" name="dias" value="180">
                    <button type="submit" class="btn btn-outline" style="font-size: 12px; padding: 7px 14px; background: #ffffff;">
                        <i class="fa-solid fa-plus" style="color: #10b981;"></i> +180 Días (6 Meses)
                    </button>
                </form>

                <form action="{{ route('superadmin.talleres.extender-licencia', $taller) }}" method="POST">
                    @csrf
                    <input type="hidden" name="dias" value="365">
                    <button type="submit" class="btn btn-amber" style="font-size: 12px; padding: 7px 14px;">
                        <i class="fa-solid fa-crown"></i> +365 Días (1 Año Completo)
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Lista de Usuarios y Colaboradores del Taller -->
    <div class="table-card" style="margin-bottom: 24px;">
        <div class="card-header" style="padding: 16px 20px; margin-bottom: 0;">
            <div>
                <h3 style="font-size: 15px; font-weight: 800; color: var(--text-main, #0f172a); margin: 0 0 2px 0;">
                    <i class="fa-solid fa-user-gear" style="color: #0284c7; margin-right: 6px;"></i>
                    Usuarios y Personal del Taller ({{ $taller->usuarios->count() }})
                </h3>
                <p style="font-size: 12px; color: var(--text-muted, #64748b); margin: 0;">Administradores y técnicos registrados con credenciales de acceso.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre / Colaborador</th>
                        <th>Correo Electrónico</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Sesión en Vivo</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taller->usuarios as $user)
                        <tr>
                            <td>
                                <strong style="color: var(--text-main, #0f172a); font-size: 13.5px; display: block;">{{ $user->nombre_completo }}</strong>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-size: 12px; color: var(--text-main, #334155);">{{ $user->email }}</span>
                            </td>
                            <td>
                                <span style="font-size: 12px; color: var(--text-muted, #64748b);">{{ $user->telefono ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($user->esAdminTaller())
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                                        <i class="fa-solid fa-user-shield"></i> Administrador
                                    </span>
                                @elseif($user->esTecnico())
                                    <span class="badge" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0;">
                                        <i class="fa-solid fa-screwdriver-wrench"></i> Técnico
                                    </span>
                                @else
                                    <span class="badge badge-slate">{{ ucfirst($user->rol) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->estaEnLinea())
                                    <span class="badge" style="background: #ecfdf5; color: #047857; border: 1.5px solid #a7f3d0; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;">
                                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #10b981; display: inline-block; box-shadow: 0 0 8px #10b981;"></span>
                                        En Línea
                                    </span>
                                @else
                                    <span class="badge" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">Desconectado</span>
                                @endif
                            </td>
                            <td>
                                @if($user->esta_activo)
                                    <span class="badge badge-active">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($user->estaEnLinea())
                                    <form action="{{ route('superadmin.seguridad.desconectar', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Cerrar la sesión activa de {{ $user->nombre_completo }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-icon" title="Cerrar sesión activa" style="color: #ea580c; border-color: #fed7aa; background: #fff7ed;">
                                            <i class="fa-solid fa-power-off"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 32px; color: var(--text-muted, #94a3b8);">
                                No hay colaboradores registrados en este taller.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Últimas Órdenes del Taller -->
    <div class="table-card">
        <div class="card-header" style="padding: 16px 20px; margin-bottom: 0;">
            <div>
                <h3 style="font-size: 15px; font-weight: 800; color: var(--text-main, #0f172a); margin: 0 0 2px 0;">
                    <i class="fa-solid fa-clipboard-list" style="color: #10b981; margin-right: 6px;"></i>
                    Últimas Órdenes Registradas por la Empresa
                </h3>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Código / Fecha</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taller->ordenesTrabajo as $ot)
                        <tr>
                            <td>
                                <strong style="color: #0284c7; font-size: 13.5px;">{{ $ot->codigo_orden }}</strong>
                                <span style="font-size: 11px; color: var(--text-muted, #94a3b8); display: block;">{{ $ot->fecha_ingreso ? $ot->fecha_ingreso->format('d/m/Y') : '' }}</span>
                            </td>
                            <td>
                                <span style="font-weight: 700; color: var(--text-main, #334155);">{{ $ot->cliente?->nombre_completo ?? 'Cliente general' }}</span>
                            </td>
                            <td>
                                @if($ot->estado === 'finalizado')
                                    <span class="badge badge-finished">Finalizado</span>
                                @elseif($ot->estado === 'en_proceso')
                                    <span class="badge badge-process">En Proceso</span>
                                @else
                                    <span class="badge badge-pending">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 32px; color: var(--text-muted, #94a3b8);">
                                Este taller aún no ha registrado órdenes de trabajo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
