@extends('layouts.app')

@section('titulo', 'Editar Colaborador / Técnico')

@section('contenido')
<div style="max-width: 760px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Editar: {{ $personal->nombre_completo }}</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Actualiza los datos, rol o restablece la contraseña del colaborador.</p>
        </div>

        <a href="{{ route('personal.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a la Lista</span>
        </a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('personal.update', $personal) }}">
            @csrf
            @method('PUT')

            <div class="form-grid-2">
                
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre', $personal->nombre) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Apellido *</label>
                    <input type="text" name="apellido" value="{{ old('apellido', $personal->apellido) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico (Login) *</label>
                    <input type="email" name="email" value="{{ old('email', $personal->email) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono / WhatsApp</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $personal->telefono) }}"
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="password"
                           class="form-input-text" placeholder="Dejar vacío para mantener la actual">
                </div>

                <div class="form-group">
                    <label class="form-label">Rol en el Taller *</label>
                    <select name="rol" required class="form-input-text">
                        <option value="tecnico" {{ $personal->rol === 'tecnico' ? 'selected' : '' }}>🛠️ Técnico Operativo</option>
                        <option value="administrador" {{ $personal->rol === 'administrador' ? 'selected' : '' }}>👔 Administrador</option>
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #334155; padding: 12px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer;">
                        <input type="checkbox" name="esta_activo" value="1" {{ $personal->esta_activo ? 'checked' : '' }} style="accent-color: #10b981;">
                        <span>Usuario Activo (Permite iniciar sesión en el taller)</span>
                    </label>
                </div>

            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                <a href="{{ route('personal.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                    <i class="fa-solid fa-save"></i>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
