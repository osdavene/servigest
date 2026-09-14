@extends('layouts.app')

@section('titulo', 'Gestionar Licencia / Plan')

@section('contenido')
<div style="max-width: 760px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Plan de Licencia: {{ $licencia->nombre }}</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Detalle y configuración de parámetros de la licencia.</p>
        </div>

        <a href="{{ route('superadmin.licencias.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a Licencias</span>
        </a>
    </div>

    @if($estaBloqueada)
        <div style="padding: 16px 20px; background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1.5px solid #fde68a; border-radius: 16px; margin-bottom: 22px; display: flex; align-items: flex-start; gap: 14px;">
            <div style="width: 40px; height: 40px; background: #d97706; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; flex-shrink: 0;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <strong style="color: #78350f; font-size: 14px; display: block;">Licencia Bloqueada por Suscripción Activa</strong>
                <p style="font-size: 12.5px; color: #92400e; margin-top: 4px; line-height: 1.5;">
                    Esta licencia está actualmente contratada y activa en <strong>{{ $talleresActivosCount }} {{ Str::plural('empresa', $talleresActivosCount) }}</strong>. Por protección legal, contractual y de facturación, sus parámetros (días de vigencia, precio y límites) están protegidos contra edición.
                </p>
                <p style="font-size: 12px; color: #b45309; margin-top: 6px; font-weight: 700;">
                    💡 Para ofrecer nuevos precios o días a futuros clientes, crea un nuevo plan en el catálogo.
                </p>
            </div>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('superadmin.licencias.update', $licencia) }}">
            @csrf
            @method('PUT')

            <div class="form-grid-2">
                
                <div class="form-group form-group-full">
                    <label class="form-label">Nombre del Plan / Licencia *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $licencia->nombre) }}" required {{ $estaBloqueada ? 'readonly' : '' }}
                           class="form-input-text" style="{{ $estaBloqueada ? 'background: #f1f5f9; cursor: not-allowed;' : '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Duración en Días de Servicio *</label>
                    <input type="number" name="dias_duracion" value="{{ old('dias_duracion', $licencia->dias_duracion) }}" min="1" required {{ $estaBloqueada ? 'readonly' : '' }}
                           class="form-input-text" style="{{ $estaBloqueada ? 'background: #f1f5f9; cursor: not-allowed;' : '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Precio de Venta ($ COP / Moneda Local) *</label>
                    <input type="number" step="0.01" name="precio" value="{{ old('precio', $licencia->precio) }}" min="0" required {{ $estaBloqueada ? 'readonly' : '' }}
                           class="form-input-text" style="{{ $estaBloqueada ? 'background: #f1f5f9; cursor: not-allowed;' : '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Límite de Usuarios / Técnicos</label>
                    <input type="number" name="limite_usuarios" value="{{ old('limite_usuarios', $licencia->limite_usuarios) }}" min="1" {{ $estaBloqueada ? 'readonly' : '' }}
                           class="form-input-text" placeholder="Ilimitado" style="{{ $estaBloqueada ? 'background: #f1f5f9; cursor: not-allowed;' : '' }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #334155; padding: 10px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; {{ $estaBloqueada ? 'cursor: not-allowed;' : 'cursor: pointer;' }}">
                        <input type="checkbox" name="esta_activo" value="1" {{ $licencia->esta_activo ? 'checked' : '' }} {{ $estaBloqueada ? 'disabled' : '' }} style="accent-color: #10b981;">
                        <span>Plan Activo</span>
                    </label>
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Descripción y Beneficios del Plan</label>
                    <textarea name="descripcion" rows="3" class="form-textarea" {{ $estaBloqueada ? 'readonly' : '' }} style="{{ $estaBloqueada ? 'background: #f1f5f9; cursor: not-allowed;' : '' }}">{{ old('descripcion', $licencia->descripcion) }}</textarea>
                </div>

            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                <a href="{{ route('superadmin.licencias.index') }}" class="btn btn-outline">Volver a la Lista</a>
                @if(!$estaBloqueada)
                    <button type="submit" class="btn btn-amber" style="padding: 12px 28px;">
                        <i class="fa-solid fa-save"></i>
                        <span>Guardar Cambios</span>
                    </button>
                @endif
            </div>
        </form>
    </div>

</div>
@endsection
