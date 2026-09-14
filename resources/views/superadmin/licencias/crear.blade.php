@extends('layouts.app')

@section('titulo', 'Crear Nueva Licencia / Plan')

@section('contenido')
<div style="max-width: 760px; margin: 0 auto;">

    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Crear Nuevo Plan de Licencia</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Define el nombre, duración en días, costo y límites de tu nuevo paquete comercial.</p>
        </div>

        <a href="{{ route('superadmin.licencias.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a Licencias</span>
        </a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('superadmin.licencias.store') }}">
            @csrf

            <div class="form-grid-2">
                
                <div class="form-group form-group-full">
                    <label class="form-label">Nombre del Plan / Licencia *</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" required
                           class="form-input-text" placeholder="Ej. Plan Trimestral Pyme, Licencia Anual VIP, Plan 60 Días...">
                </div>

                <div class="form-group">
                    <label class="form-label">Duración en Días de Servicio *</label>
                    <input type="number" name="dias_duracion" value="{{ old('dias_duracion', 30) }}" min="1" required
                           class="form-input-text" placeholder="Ej. 30, 90, 180, 365...">
                    <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 4px;">Cantidad de días que el taller tendrá acceso activo.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Precio de Venta ($ COP / Moneda Local) *</label>
                    <input type="number" step="0.01" name="precio" value="{{ old('precio', 0) }}" min="0" required
                           class="form-input-text" placeholder="Ej. 50000">
                </div>

                <div class="form-group">
                    <label class="form-label">Límite de Usuarios / Técnicos Permitidos</label>
                    <input type="number" name="limite_usuarios" value="{{ old('limite_usuarios') }}" min="1"
                           class="form-input-text" placeholder="Dejar vacío para ILIMITADO">
                </div>

                <div class="form-group">
                    <label class="form-label">Tipo de Licencia</label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 700; color: #334155; padding: 10px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; cursor: pointer;">
                        <input type="checkbox" name="es_prueba" value="1" {{ old('es_prueba') ? 'checked' : '' }} style="accent-color: #d97706;">
                        <span>Marcar como Periodo de Prueba (Demo)</span>
                    </label>
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Descripción y Beneficios del Plan</label>
                    <textarea name="descripcion" rows="3" class="form-textarea" placeholder="Describe qué incluye esta licencia (ej. Soporte prioritario, reportes WhatsApp ilimitados, etc.)...">{{ old('descripcion') }}</textarea>
                </div>

            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border);">
                <a href="{{ route('superadmin.licencias.index') }}" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-amber" style="padding: 12px 28px;">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>Guardar y Publicar Licencia</span>
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
