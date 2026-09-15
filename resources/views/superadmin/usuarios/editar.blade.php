@extends('layouts.app')

@section('titulo', 'Editar Super Administrador: ' . $usuario->nombre_completo)

@section('contenido')
<div style="max-width: 760px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 900; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);">
                {{ strtoupper(substr($usuario->nombre, 0, 1)) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <h2 style="font-size: 20px; font-weight: 900; color: var(--text-main, #0f172a); margin: 0;">{{ $usuario->nombre_completo }}</h2>
                    @if($usuario->id === auth()->id())
                        <span class="badge" style="background: rgba(245, 158, 11, 0.15); color: #b45309; border: 1px solid #fde68a;">Tu Cuenta</span>
                    @endif
                </div>
                <p style="font-size: 13px; color: var(--text-muted, #64748b); margin: 0;">Actualización de datos personales y restablecimiento de contraseña maestra.</p>
            </div>
        </div>

        <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <form method="POST" action="{{ route('superadmin.usuarios.update', $usuario) }}">
        @csrf
        @method('PUT')

        <!-- 1. DATOS PERSONALES -->
        <div class="card" style="padding: 24px; margin-bottom: 20px;">
            <div class="card-header" style="margin-bottom: 18px; padding-bottom: 12px;">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px; font-size: 15px;">
                        <i class="fa-solid fa-user-gear" style="color: #0284c7;"></i>
                        <span>1. Información del Administrador</span>
                    </h3>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Apellido *</label>
                    <input type="text" name="apellido" value="{{ old('apellido', $usuario->apellido) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico (Login Maestro) *</label>
                    <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono / WhatsApp</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                           class="form-input-text">
                </div>

                @if($usuario->id !== auth()->id())
                    <div class="form-group form-group-full" style="margin-bottom: 0;">
                        <label class="form-label">Estado de la Cuenta</label>
                        <div style="display: flex; align-items: center; gap: 10px; margin-top: 6px;">
                            <input type="hidden" name="esta_activo" value="0">
                            <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13.5px; font-weight: 700; color: var(--text-main, #0f172a);">
                                <input type="checkbox" name="esta_activo" value="1" {{ old('esta_activo', $usuario->esta_activo) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #d97706;">
                                <span>Cuenta Activa (Permite iniciar sesión en la consola maestra)</span>
                            </label>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. CAMBIO DE CONTRASEÑA -->
        <div class="card" style="padding: 24px; margin-bottom: 24px; border-color: #fde68a;">
            <div class="card-header" style="margin-bottom: 18px; padding-bottom: 12px; border-bottom-color: #fde68a;">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px; font-size: 15px; color: #92400e;">
                        <i class="fa-solid fa-key" style="color: #d97706;"></i>
                        <span>2. Cambiar Contraseña Maestra</span>
                    </h3>
                    <p style="font-size: 12px; color: var(--text-muted, #64748b);">Deja estos campos en blanco si deseas mantener la contraseña actual intacta.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nueva Contraseña (Mínimo 8 caracteres)</label>
                    <input type="password" name="password" placeholder="••••••••••••"
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••••••"
                           class="form-input-text">
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px;">
            <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-amber">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>

</div>
@endsection
