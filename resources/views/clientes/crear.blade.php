@extends('layouts.app')

@section('titulo', 'Registrar Nuevo Cliente')

@section('contenido')
<div style="max-width: 860px; margin: 0 auto;">

    <!-- Encabezado -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Registrar Nuevo Cliente</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Datos de contacto y georreferenciación para atención en taller y visitas a domicilio.</p>
        </div>

        <a href="{{ route('clientes.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a Clientes</span>
        </a>
    </div>

    <form method="POST" action="{{ route('clientes.store') }}">
        @csrf

        <!-- 1. DATOS PERSONALES Y CONTACTO -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-user-check" style="color: #0284c7;"></i>
                        <span>1. Información Personal y Canales de Comunicación</span>
                    </h3>
                    <p>Nombre, documento de identidad y teléfonos para reportes por WhatsApp.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group form-group-full">
                    <label class="form-label">Nombre Completo del Cliente *</label>
                    <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}" required
                           class="form-input-text" placeholder="Ej. Juan Pérez Rodríguez">
                </div>

                <div class="form-group">
                    <label class="form-label">Cédula / Documento de Identidad</label>
                    <input type="text" name="identificacion" value="{{ old('identificacion') }}"
                           class="form-input-text" placeholder="Ej. CC 1020304050">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono / WhatsApp Principal *</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" required
                           class="form-input-text" placeholder="Ej. 573001234567">
                    <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 4px;">Incluye indicativo de país (ej. 57 para Colombia).</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono Secundario / Fijo</label>
                    <input type="text" name="telefono_secundario" value="{{ old('telefono_secundario') }}"
                           class="form-input-text" placeholder="Ej. 6013334455">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-input-text" placeholder="cliente@correo.com">
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
                    <input type="text" name="direccion" value="{{ old('direccion') }}" required
                           class="form-input-text" placeholder="Ej. Calle 127 # 45-20 Apto 302">
                </div>

                <div class="form-group">
                    <label class="form-label">Barrio / Sector</label>
                    <input type="text" name="barrio" value="{{ old('barrio') }}"
                           class="form-input-text" placeholder="Ej. Unicentro, Poblado, Chapinero...">
                </div>

                <div class="form-group">
                    <label class="form-label">Ciudad / Municipio</label>
                    <input type="text" name="ciudad" value="{{ old('ciudad', auth()->user()->taller?->ciudad ?? 'Bogotá') }}"
                           class="form-input-text">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Notas de Acceso o Referencias Adicionales</label>
                    <textarea name="notas_adicionales" rows="2" class="form-textarea" placeholder="Ej. Conjunto cerrado, llamar al citófono 302, casa de rejas blancas...">{{ old('notas_adicionales') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; margin-bottom: 40px;">
            <a href="{{ route('clientes.index') }}" class="btn btn-outline" style="padding: 12px 24px;">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 14px;">
                <i class="fa-solid fa-user-plus"></i>
                <span>Registrar Cliente</span>
            </button>
        </div>

    </form>

</div>
@endsection
