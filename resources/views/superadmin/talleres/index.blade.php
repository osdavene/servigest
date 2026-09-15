@extends('layouts.app')

@section('titulo', 'Gestión de Talleres SaaS')

@section('contenido')
<div>

    <!-- Grid de Métricas de Talleres SaaS -->
    <div class="grid-kpi">
        
        <a href="{{ route('superadmin.talleres.index') }}" class="kpi-card" title="Ver todos los talleres">
            <div class="kpi-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fa-solid fa-building-shield"></i>
            </div>
            <div>
                <div class="kpi-title">Total Empresas</div>
                <div class="kpi-num" style="color: #d97706;">{{ $totalTalleres }}</div>
            </div>
        </a>

        <a href="{{ route('superadmin.talleres.index', ['estado_suscripcion' => 'activo']) }}" class="kpi-card" title="Filtrar Talleres Activos">
            <div class="kpi-icon" style="background: #ecfdf5; color: #10b981;">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="kpi-title">Suscripciones Activas</div>
                <div class="kpi-num" style="color: #10b981;">{{ $talleresActivos }}</div>
            </div>
        </a>

        <a href="{{ route('superadmin.talleres.index', ['estado_suscripcion' => 'periodo_prueba']) }}" class="kpi-card" title="Filtrar Periodo de Prueba">
            <div class="kpi-icon" style="background: #eff6ff; color: #2563eb;">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <div class="kpi-title">En Periodo de Prueba</div>
                <div class="kpi-num" style="color: #2563eb;">{{ $talleresPrueba }}</div>
            </div>
        </a>

        <a href="{{ route('superadmin.talleres.index', ['estado_suscripcion' => 'suspendido']) }}" class="kpi-card" title="Filtrar Suspendidos / Vencidos">
            <div class="kpi-icon" style="background: #fee2e2; color: #ef4444;">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <div class="kpi-title">Suspendidos</div>
                <div class="kpi-num" style="color: #ef4444;">{{ $talleresSuspendidos }}</div>
            </div>
        </a>

        <a href="{{ route('superadmin.seguridad.index') }}" class="kpi-card" title="Ver Auditoría y Sesiones">
            <div class="kpi-icon" style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="kpi-title">Usuarios En Línea</div>
                <div class="kpi-num" style="color: #16a34a; display: flex; align-items: center; gap: 6px;">
                    {{ $usuariosEnLinea }}
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a; box-shadow: 0 0 8px #16a34a;"></span>
                </div>
            </div>
        </a>

    </div>

    <div class="filter-bar">
        <form method="GET" action="{{ route('superadmin.talleres.index') }}" class="search-group" style="flex: 1; max-width: 620px; display: flex; gap: 10px; flex-wrap: wrap;">
            <div class="search-input-wrapper" style="flex: 1; min-width: 280px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" 
                       name="buscar" 
                       value="{{ $busqueda ?? '' }}" 
                       oninput="autoBuscar(this.form)"
                       placeholder="🔍 Nombre de taller, NIT, correo, teléfono, ciudad..." 
                       class="input-control"
                       autofocus>
            </div>

            <select name="estado_suscripcion" onchange="this.form.submit()" class="select-control" style="min-width: 170px;">
                <option value="">🏢 Todos los Estados</option>
                <option value="activo" {{ ($estadoSuscripcion === 'activo') ? 'selected' : '' }}>🟢 Activo</option>
                <option value="periodo_prueba" {{ ($estadoSuscripcion === 'periodo_prueba') ? 'selected' : '' }}>🔵 Prueba Gratuita</option>
                <option value="suspendido" {{ ($estadoSuscripcion === 'suspendido') ? 'selected' : '' }}>🔴 Suspendido / Vencido</option>
            </select>

            @if(!empty($busqueda) || !empty($estadoSuscripcion))
                <a href="{{ route('superadmin.talleres.index') }}" class="btn btn-outline btn-icon" title="Limpiar búsqueda">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>

        <a href="{{ route('superadmin.talleres.create') }}" class="btn btn-amber">
            <i class="fa-solid fa-plus"></i>
            <span>Nuevo Taller</span>
        </a>
    </div>

    <!-- Tabla de Talleres con Diseño de Alta Fidelidad -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Taller / Razón Social</th>
                        <th>Contacto Principal</th>
                        <th>Estado Suscripción</th>
                        <th>Vencimiento</th>
                        <th style="text-align: center;">Usuarios</th>
                        <th style="text-align: center;">Clientes</th>
                        <th style="text-align: center;">Órdenes</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($talleres as $taller)
                        <tr data-href="{{ route('superadmin.talleres.show', $taller) }}" class="clickable-row" title="Clic para ver detalles de {{ $taller->nombre_comercial }}">
                            <td>
                                <strong style="color: var(--text-main, #0f172a); font-size: 14px; display: block;">{{ $taller->nombre_comercial }}</strong>
                                <span style="font-size: 11px; color: var(--text-muted, #94a3b8);">{{ $taller->identificacion_fiscal ? 'NIT: ' . $taller->identificacion_fiscal : 'Sin NIT' }} • {{ $taller->ciudad }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main, #334155);">{{ $taller->telefono }}</div>
                                <span style="font-size: 11px; color: var(--text-muted, #94a3b8);">{{ $taller->email }}</span>
                            </td>
                            <td>
                                @if($taller->estado_suscripcion === 'activo')
                                    <span class="badge badge-active">
                                        <i class="fa-solid fa-circle-check"></i> Activo
                                    </span>
                                @elseif($taller->estado_suscripcion === 'periodo_prueba')
                                    <span class="badge badge-pending">
                                        <i class="fa-solid fa-clock"></i> Prueba
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fa-solid fa-circle-xmark"></i> Suspendido
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span style="font-size: 13px; font-weight: 700; color: var(--text-main, #0f172a); display: block;">
                                    {{ $taller->fecha_fin_suscripcion?->format('d/m/Y') }}
                                </span>
                                @if($taller->dias_restantes_licencia <= 7)
                                    <span class="badge badge-danger" style="font-size: 9px;">¡{{ $taller->dias_restantes_licencia }} días!</span>
                                @else
                                    <span style="font-size: 11px; color: var(--text-muted, #64748b);">{{ $taller->dias_restantes_licencia }} días rest.</span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-slate">{{ $taller->usuarios_count }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-slate">{{ $taller->clientes_count }}</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge badge-slate">{{ $taller->ordenes_trabajo_count }}</span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="{{ route('superadmin.talleres.show', $taller) }}" class="btn btn-outline btn-icon" title="Ver Detalle">
                                        <i class="fa-solid fa-eye" style="color: #0284c7;"></i>
                                    </a>
                                    <a href="{{ route('superadmin.talleres.edit', $taller) }}" class="btn btn-outline btn-icon" title="Editar">
                                        <i class="fa-solid fa-pen-to-square" style="color: #d97706;"></i>
                                    </a>
                                    <form action="{{ route('superadmin.talleres.toggle-estado', $taller) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-icon" title="{{ $taller->estado_suscripcion === 'activo' ? 'Suspender' : 'Activar' }}">
                                            <i class="fa-solid {{ $taller->estado_suscripcion === 'activo' ? 'fa-ban text-rose-600' : 'fa-check text-emerald-600' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #94a3b8;">
                                <i class="fa-solid fa-shop-slash" style="font-size: 32px; display: block; margin-bottom: 8px;"></i>
                                No se encontraron talleres suscritos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($talleres->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--border);">
                {{ $talleres->links() }}
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
