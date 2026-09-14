@extends('layouts.app')

@section('titulo', 'Gestión de Licencias & Planes')

@section('contenido')
<div>

    <div class="filter-bar">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Planes y Licencias del Sistema</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Define los paquetes de tiempo, precios y límites. Las licencias activas en empresas quedan bloqueadas para preservar la integridad contractual.</p>
        </div>

        <a href="{{ route('superadmin.licencias.create') }}" class="btn btn-amber">
            <i class="fa-solid fa-plus"></i>
            <span>Crear Nueva Licencia</span>
        </a>
    </div>

    <!-- Grid de Tarjetas de Licencias Disponibles -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 32px;">
        @forelse($planes as $plan)
            <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-color: {{ $plan->talleres_count > 0 ? '#fde68a' : 'var(--border)' }}; background: {{ $plan->talleres_count > 0 ? 'linear-gradient(180deg, #fffdfa 0%, #ffffff 100%)' : '#ffffff' }}; margin-bottom: 0;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        @if($plan->talleres_count > 0)
                            <span class="badge badge-pending" style="font-size: 10px;">
                                <i class="fa-solid fa-lock"></i> En Uso Activo ({{ $plan->talleres_count }} {{ Str::plural('taller', $plan->talleres_count) }})
                            </span>
                        @else
                            <span class="badge {{ $plan->es_prueba ? 'badge-pending' : ($plan->esta_activo ? 'badge-active' : 'badge-slate') }}">
                                {{ $plan->es_prueba ? 'Demo Gratuita' : ($plan->esta_activo ? 'Activo' : 'Inactivo') }}
                            </span>
                        @endif

                        <span style="font-size: 12px; font-weight: 800; color: #d97706; background: rgba(245, 158, 11, 0.1); padding: 4px 10px; border-radius: 8px;">
                            <i class="fa-solid fa-calendar-days"></i> {{ $plan->dias_duracion }} Días
                        </span>
                    </div>

                    <strong style="font-size: 18px; color: #0f172a; display: block;">{{ $plan->nombre }}</strong>
                    
                    <div style="margin: 14px 0 10px; display: flex; align-items: baseline; gap: 6px;">
                        <span style="font-size: 28px; font-weight: 900; color: #0f172a;">{{ $plan->precio_formateado }}</span>
                        <span style="font-size: 12px; color: #64748b;">/ periodo</span>
                    </div>

                    <p style="font-size: 12.5px; color: #64748b; margin-bottom: 16px; line-height: 1.5;">
                        {{ $plan->descripcion ?? 'Sin descripción.' }}
                    </p>

                    <div style="font-size: 12px; color: #334155; display: flex; flex-direction: column; gap: 8px; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border);">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-users" style="color: #0284c7; width: 16px;"></i>
                            <span>Usuarios: <strong>{{ $plan->limite_usuarios ? "Hasta {$plan->limite_usuarios} usuarios" : 'Ilimitados' }}</strong></span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-clock" style="color: #10b981; width: 16px;"></i>
                            <span>Vigencia: <strong>{{ $plan->dias_duracion }} días de servicio</strong></span>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                    @if($plan->talleres_count > 0)
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #b45309; font-weight: 700;">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Bloqueada por suscripción activa</span>
                        </div>
                        <a href="{{ route('superadmin.licencias.edit', $plan) }}" class="btn btn-outline" style="font-size: 11px; padding: 6px 12px; color: #64748b;">
                            <i class="fa-solid fa-eye"></i> Ver
                        </a>
                    @else
                        <a href="{{ route('superadmin.licencias.edit', $plan) }}" class="btn btn-outline" style="font-size: 12px; padding: 6px 14px;">
                            <i class="fa-solid fa-pen-to-square" style="color: #d97706;"></i>
                            <span>Editar Plan</span>
                        </a>

                        <form action="{{ route('superadmin.licencias.destroy', $plan) }}" method="POST" onsubmit="return confirm('¿Eliminar este plan de licencia?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline btn-icon" title="Eliminar" style="color: #ef4444;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 48px 20px;">
                <i class="fa-solid fa-award" style="font-size: 36px; color: #94a3b8; margin-bottom: 12px; display: block;"></i>
                <h4 style="font-size: 16px; font-weight: 800; color: #334155;">No hay planes de licencia creados</h4>
                <p style="font-size: 13px; color: #94a3b8; margin-top: 4px;">Crea tus paquetes de días y precios para empezar a vender licencias a talleres.</p>
            </div>
        @endforelse
    </div>

</div>
@endsection
