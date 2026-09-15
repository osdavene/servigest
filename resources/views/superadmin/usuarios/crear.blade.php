@extends('layouts.app')

@section('titulo', 'Registrar Nuevo Super Administrador')

@section('contenido')
<div style="max-width: 760px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #f59e0b, #d97706); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);">
                <i class="fa-solid fa-crown"></i>
            </div>
            <div>
                <h2 style="font-size: 20px; font-weight: 900; color: var(--text-main, #0f172a); margin: 0;">Alta de Super Administrador</h2>
                <p style="font-size: 13px; color: var(--text-muted, #64748b); margin: 0;">Crea una nueva cuenta con permisos globales de gestión de la plataforma SaaS.</p>
            </div>
        </div>

        <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver</span>
        </a>
    </div>

    <form method="POST" action="{{ route('superadmin.usuarios.store') }}" class="card" style="padding: 24px;">
        @csrf

        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Nombre *</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required autofocus
                       placeholder="Ej: Oscar David" class="form-input-text">
            </div>

            <div class="form-group">
                <label class="form-label">Apellido *</label>
                <input type="text" name="apellido" value="{{ old('apellido') }}" required
                       placeholder="Ej: Pérez" class="form-input-text">
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico (Login Maestro) *</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       placeholder="admin@servigest.com" class="form-input-text">
            </div>

            <div class="form-group">
                <label class="form-label">Teléfono / WhatsApp</label>
                <input type="text" name="telefono" value="{{ old('telefono') }}"
                       placeholder="Ej: 573001234567" class="form-input-text">
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña Maestra (Mínimo 8 caracteres) *</label>
                <input type="password" name="password" required
                       placeholder="••••••••••••" class="form-input-text">
            </div>

            <div class="form-group">
                <label class="form-label">Confirmar Contraseña Maestra *</label>
                <input type="password" name="password_confirmation" required
                       placeholder="••••••••••••" class="form-input-text">
            </div>
        </div>

        <div style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25); padding: 14px; border-radius: 12px; margin-top: 10px; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 10px;">
            <i class="fa-solid fa-triangle-exclamation" style="color: #d97706; margin-top: 2px;"></i>
            <span style="font-size: 12.5px; color: #92400e; line-height: 1.45;">
                <strong>Advertencia de Privilegios:</strong> Esta cuenta tendrá acceso ilimitado a todos los talleres clientes, licencias, respaldos y auditorías de seguridad del sistema.
            </span>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 16px; border-top: 1px solid var(--border);">
            <a href="{{ route('superadmin.usuarios.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-amber">
                <i class="fa-solid fa-crown"></i>
                <span>Crear Super Administrador</span>
            </button>
        </div>
    </form>

</div>
@endsection
