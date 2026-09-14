@extends('layouts.app')

@section('titulo', 'Editar Empresa / Taller')

@section('contenido')
<div style="max-width: 900px; margin: 0 auto;">

    <!-- Encabezado de la Sección -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Gestionar Taller: {{ $taller->nombre_comercial }}</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Renovación de licencia, vigencia de suscripción y datos de la empresa.</p>
        </div>

        <a href="{{ route('superadmin.talleres.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a la Lista</span>
        </a>
    </div>

    <form method="POST" action="{{ route('superadmin.talleres.update', $taller) }}">
        @csrf
        @method('PUT')

        <!-- 1. GESTIÓN Y RENOVACIÓN DE LICENCIA -->
        <div class="card" style="border-color: #fde68a; background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);">
            <div class="card-header" style="border-bottom-color: #fde68a;">
                <div>
                    <h3 style="color: #92400e; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-clock-rotate-left" style="color: #d97706;"></i>
                        <span>1. Vigencia de Licencia y Estado de Suscripción</span>
                    </h3>
                    <p>Extiende el tiempo de compra o ajusta el estado operativo del cliente.</p>
                </div>
            </div>

            <!-- Botones de Renovación Rápida -->
            <label class="form-label" style="color: #92400e; margin-bottom: 10px;">Renovación y Extensión Rápida de Días:</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
                
                <div class="plan-card" onclick="extenderLicencia(30, 'activo', this)" style="border: 1.5px solid var(--border); background: #fff; padding: 14px; border-radius: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 6px;">+1 Mes</span>
                    <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 6px;">+30 Días</strong>
                    <span style="font-size: 11px; color: #64748b;">Renovación Mensual</span>
                </div>

                <div class="plan-card" onclick="extenderLicencia(180, 'activo', this)" style="border: 1.5px solid var(--border); background: #fff; padding: 14px; border-radius: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #ecfdf5; color: #065f46; padding: 2px 8px; border-radius: 6px;">+6 Meses</span>
                    <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 6px;">+180 Días</strong>
                    <span style="font-size: 11px; color: #64748b;">Renovación Semestral</span>
                </div>

                <div class="plan-card" onclick="extenderLicencia(365, 'activo', this)" style="border: 1.5px solid var(--border); background: #fff; padding: 14px; border-radius: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fdf4ff; color: #86198f; padding: 2px 8px; border-radius: 6px;">+1 Año</span>
                    <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 6px;">+365 Días</strong>
                    <span style="font-size: 11px; color: #64748b;">Renovación Anual</span>
                </div>

                <div class="plan-card" onclick="suspenderTaller(this)" style="border: 1.5px solid var(--border); background: #fff; padding: 14px; border-radius: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fee2e2; color: #991b1b; padding: 2px 8px; border-radius: 6px;">Pausar</span>
                    <strong style="font-size: 16px; color: #991b1b; display: block; margin-top: 6px;">Suspender</strong>
                    <span style="font-size: 11px; color: #64748b;">Bloquear Acceso</span>
                </div>

            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Estado de la Suscripción *</label>
                    <select name="estado_suscripcion" id="inputEstadoEdit" required class="form-input-text">
                        <option value="activo" {{ $taller->estado_suscripcion === 'activo' ? 'selected' : '' }}>🟢 Activo (Licencia Pagada)</option>
                        <option value="periodo_prueba" {{ $taller->estado_suscripcion === 'periodo_prueba' ? 'selected' : '' }}>🟡 Periodo de Prueba (Demo)</option>
                        <option value="suspendido" {{ $taller->estado_suscripcion === 'suspendido' ? 'selected' : '' }}>🔴 Suspendido / Bloqueado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha Límite de Vencimiento *</label>
                    <input type="date" name="fecha_vencimiento_suscripcion" id="inputVencimientoEdit" required
                           value="{{ old('fecha_vencimiento_suscripcion', $taller->fecha_vencimiento_suscripcion?->format('Y-m-d')) }}"
                           class="form-input-text">
                </div>
            </div>
        </div>

        <!-- 2. DATOS DE LA EMPRESA / TALLER -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-building" style="color: #0284c7;"></i>
                        <span>2. Información Comercial del Taller</span>
                    </h3>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group form-group-full">
                    <label class="form-label">Nombre Comercial del Taller *</label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $taller->nombre_comercial) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Identificación Fiscal / NIT / RUT</label>
                    <input type="text" name="identificacion_fiscal" value="{{ old('identificacion_fiscal', $taller->identificacion_fiscal) }}"
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono de Contacto (WhatsApp) *</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $taller->telefono) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Oficial de la Empresa *</label>
                    <input type="email" name="email" value="{{ old('email', $taller->email) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Ciudad / Municipio</label>
                    <input type="text" name="ciudad" value="{{ old('ciudad', $taller->ciudad) }}"
                           class="form-input-text">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Dirección Física del Taller</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $taller->direccion) }}"
                           class="form-input-text">
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; margin-bottom: 40px;">
            <a href="{{ route('superadmin.talleres.index') }}" class="btn btn-outline" style="padding: 12px 24px;">
                Cancelar
            </a>
            <button type="submit" class="btn btn-amber" style="padding: 12px 32px; font-size: 14px;">
                <i class="fa-solid fa-save"></i>
                <span>Guardar Cambios</span>
            </button>
        </div>
    </form>

</div>

<script>
    function extenderLicencia(dias, estado, elemento) {
        document.querySelectorAll('.plan-card').forEach(function(card) {
            card.style.borderColor = 'var(--border)';
            card.style.boxShadow = 'none';
        });

        elemento.style.borderColor = '#d97706';
        elemento.style.boxShadow = '0 0 0 2px rgba(217, 119, 6, 0.2)';

        document.getElementById('inputEstadoEdit').value = estado;

        var inputFecha = document.getElementById('inputVencimientoEdit');
        var baseDate = inputFecha.value ? new Date(inputFecha.value) : new Date();
        var hoy = new Date();
        if (baseDate < hoy) {
            baseDate = hoy;
        }

        baseDate.setDate(baseDate.getDate() + dias);
        var mes = ('0' + (baseDate.getMonth() + 1)).slice(-2);
        var dia = ('0' + baseDate.getDate()).slice(-2);
        inputFecha.value = baseDate.getFullYear() + '-' + mes + '-' + dia;
    }

    function suspenderTaller(elemento) {
        document.querySelectorAll('.plan-card').forEach(function(card) {
            card.style.borderColor = 'var(--border)';
            card.style.boxShadow = 'none';
        });

        elemento.style.borderColor = '#ef4444';
        elemento.style.boxShadow = '0 0 0 2px rgba(239, 68, 68, 0.2)';

        document.getElementById('inputEstadoEdit').value = 'suspendido';
    }
</script>
@endsection
