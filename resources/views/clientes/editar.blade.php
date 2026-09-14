@extends('layouts.app')

@section('titulo', 'Editar Cliente: ' . $cliente->nombre_completo)

@section('contenido')
<div style="max-width: 860px; margin: 0 auto;">

    <!-- Encabezado -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Editar Cliente: {{ $cliente->nombre_completo }}</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Actualizar información de contacto y domicilio.</p>
        </div>

        <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver al Perfil</span>
        </a>
    </div>

    <form method="POST" action="{{ route('clientes.update', $cliente) }}">
        @csrf
        @method('PUT')

        <!-- 1. DATOS PERSONALES Y CONTACTO -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-user-pen" style="color: #0284c7;"></i>
                        <span>1. Información Personal y Canales de Comunicación</span>
                    </h3>
                    <p>Nombre, documento de identidad y teléfonos para reportes por WhatsApp.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group form-group-full">
                    <label class="form-label">Nombre Completo del Cliente *</label>
                    <input type="text" name="nombre_completo" value="{{ old('nombre_completo', $cliente->nombre_completo) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Cédula / Documento de Identidad</label>
                    <input type="text" name="identificacion" value="{{ old('identificacion', $cliente->identificacion) }}"
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono / WhatsApp Principal *</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono Secundario / Fijo</label>
                    <input type="text" name="telefono_secundario" value="{{ old('telefono_secundario', $cliente->telefono_secundario) }}"
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email', $cliente->email) }}"
                           class="form-input-text">
                </div>
            </div>
        </div>

        <!-- 2. DIRECCIÓN Y UBICACIÓN -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-map-location-dot" style="color: #10b981;"></i>
                        <span>2. Dirección y Ubicación para Domicilios</span>
                    </h3>
                    <p>Facilita la llegada de tus técnicos a través de Google Maps y Waze.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group form-group-full">
                    <label class="form-label">Dirección de Residencia / Empresa *</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Barrio / Sector</label>
                    <input type="text" name="barrio" value="{{ old('barrio', $cliente->barrio) }}"
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Ciudad / Municipio</label>
                    <input type="text" name="ciudad" value="{{ old('ciudad', $cliente->ciudad) }}"
                           class="form-input-text">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Notas de Acceso o Referencias Adicionales</label>
                    <textarea name="notas_adicionales" rows="2" class="form-textarea">{{ old('notas_adicionales', $cliente->notas_adicionales) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; margin-bottom: 40px;">
            <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-outline" style="padding: 12px 24px;">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 14px;">
                <i class="fa-solid fa-save"></i>
                <span>Guardar Cambios</span>
            </button>
        </div>

    </form>

</div>
@endsection
