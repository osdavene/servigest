@extends('layouts.app')

@section('titulo', 'Registrar Nuevo Técnico / Colaborador')

@section('contenido')
<div style="max-width: 760px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Agregar Técnico / Colaborador</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Crea credenciales de acceso para que tu equipo pueda ingresar a atender órdenes de servicio.</p>
        </div>

        <a href="{{ route('personal.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a la Lista</span>
        </a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('personal.store') }}">
            @csrf

            <div class="form-grid-2">
                
                <div class="form-group">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="form-input-text" placeholder="Ej. Andrés">
                </div>

                <div class="form-group">
                    <label class="form-label">Apellido *</label>
                    <input type="text" name="apellido" value="{{ old('apellido') }}" required
                           class="form-input-text" placeholder="Ej. Gómez">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico (Login) *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="form-input-text" placeholder="andres@taller.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono / WhatsApp</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="form-input-text" placeholder="Ej. 573112223344">
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña de Acceso *</label>
                    <input type="password" name="password" required
                           class="form-input-text" placeholder="Mínimo 6 caracteres">
                </div>

                <div class="form-group">
                    <label class="form-label">Rol en el Taller *</label>
                    <select name="rol" required class="form-input-text">
                        <option value="tecnico" selected>🛠️ Técnico Operativo (Atención y diagnóstico)</option>
                        <option value="administrador">👔 Administrador Secundario (Acceso total)</option>
                    </select>
                </div>

            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                <a href="{{ route('personal.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Registrar Colaborador</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
