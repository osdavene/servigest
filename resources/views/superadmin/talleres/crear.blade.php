@extends('layouts.app')

@section('titulo', 'Alta de Nueva Empresa / Taller')

@section('contenido')
<div style="max-width: 900px; margin: 0 auto;">

    <!-- Encabezado de la Sección -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Registrar Nuevo Taller Cliente</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Alta de empresa inquilina en el SaaS, asignación de licencia y creación de su usuario administrador.</p>
        </div>

        <a href="{{ route('superadmin.talleres.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a la Lista</span>
        </a>
    </div>

    <form method="POST" action="{{ route('superadmin.talleres.store') }}" id="formAltaTaller">
        @csrf

        <!-- 1. SELECCIÓN DE PLAN Y LICENCIA CREADOS POR EL SUPERADMIN -->
        <div class="card" style="border-color: #fde68a; background: linear-gradient(180deg, #fffbeb 0%, #ffffff 100%);">
            <div class="card-header" style="border-bottom-color: #fde68a;">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <div>
                        <h3 style="color: #92400e; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-award" style="color: #d97706;"></i>
                            <span>1. Asignación de Licencia y Modalidad SaaS</span>
                        </h3>
                        <p>Selecciona uno de tus planes de licencia creados o define una fecha personalizada.</p>
                    </div>

                    <a href="{{ route('superadmin.licencias.index') }}" target="_blank" class="btn btn-outline" style="font-size: 11px; padding: 6px 12px; color: #d97706; border-color: #fde68a;">
                        <i class="fa-solid fa-gear"></i> Gestionar Planes
                    </a>
                </div>
            </div>

            <!-- Botones Rápidos de Planes Dinámicos creados por el SuperAdmin -->
            <label class="form-label" style="color: #92400e; margin-bottom: 10px;">Selecciona el Paquete de Licencia Contratado:</label>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
                
                @forelse($planes as $index => $plan)
                    <div class="plan-card {{ $index === 0 ? 'active' : '' }}" 
                         onclick="seleccionarPlan({{ $plan->dias_duracion }}, '{{ $plan->es_prueba ? 'periodo_prueba' : 'activo' }}', this)" 
                         style="border: {{ $index === 0 ? '2px solid #d97706' : '1.5px solid var(--border)' }}; background: #fff; padding: 14px; border-radius: 14px; cursor: pointer; text-align: center; transition: all 0.2s;">
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: {{ $plan->es_prueba ? '#fef3c7' : '#e0f2fe' }}; color: {{ $plan->es_prueba ? '#b45309' : '#0369a1' }}; padding: 2px 8px; border-radius: 6px;">
                            {{ $plan->es_prueba ? 'Demostración' : $plan->precio_formateado }}
                        </span>
                        <strong style="font-size: 15px; color: #0f172a; display: block; margin-top: 6px;">{{ $plan->nombre }}</strong>
                        <span style="font-size: 11px; color: #64748b; font-weight: 700;">{{ $plan->dias_duracion }} Días de Servicio</span>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; padding: 16px; background: #fff; border-radius: 12px; border: 1px solid var(--border); text-align: center;">
                        <span style="font-size: 13px; color: #64748b;">No hay planes registrados. <a href="{{ route('superadmin.licencias.create') }}" style="color: #0284c7; font-weight: 700;">Crear plan ahora</a></span>
                    </div>
                @endforelse

            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Estado de la Suscripción *</label>
                    <select name="estado_suscripcion" id="inputEstado" required class="form-input-text">
                        <option value="periodo_prueba" selected>🟡 Periodo de Prueba (Demo)</option>
                        <option value="activo">🟢 Activo (Licencia Pagada)</option>
                        <option value="suspendido">🔴 Suspendido / Inactivo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha Límite de Vencimiento *</label>
                    <input type="date" name="fecha_vencimiento_suscripcion" id="inputVencimiento" required
                           value="{{ old('fecha_vencimiento_suscripcion', now()->addDays($planes->first()?->dias_duracion ?? 30)->format('Y-m-d')) }}"
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
                    <p>Datos visibles en los reportes e informes de servicio técnico.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group form-group-full">
                    <label class="form-label">Nombre Comercial del Taller *</label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial') }}" required
                           class="form-input-text" placeholder="Ej. ElectroTech Especialistas, Taller San Jorge...">
                </div>

                <div class="form-group">
                    <label class="form-label">Identificación Fiscal / NIT / RUT</label>
                    <input type="text" name="identificacion_fiscal" value="{{ old('identificacion_fiscal') }}"
                           class="form-input-text" placeholder="Ej. 900.123.456-7">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono de Contacto (WhatsApp) *</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}" required
                           class="form-input-text" placeholder="Ej. 573105554433">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Oficial de la Empresa *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="form-input-text" placeholder="contacto@taller.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Ciudad / Municipio</label>
                    <input type="text" name="ciudad" value="{{ old('ciudad') }}"
                           class="form-input-text" placeholder="Ej. Bogotá, Medellín, Cali...">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Dirección Física del Taller</label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                           class="form-input-text" placeholder="Ej. Carrera 15 # 45-20 Local 102">
                </div>
            </div>
        </div>

        <!-- 3. CUENTA ADMINISTRADORA INICIAL PARA EL DUEÑO DEL TALLER -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-user-shield" style="color: #10b981;"></i>
                        <span>3. Usuario Administrador del Taller (Credenciales de Acceso)</span>
                    </h3>
                    <p>Cuenta con la que el dueño del taller ingresará a su propio panel independiente.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Nombre del Administrador *</label>
                    <input type="text" name="admin_nombre" value="{{ old('admin_nombre') }}" required
                           class="form-input-text" placeholder="Ej. Carlos">
                </div>

                <div class="form-group">
                    <label class="form-label">Apellido *</label>
                    <input type="text" name="admin_apellido" value="{{ old('admin_apellido') }}" required
                           class="form-input-text" placeholder="Ej. Mendoza">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico de Acceso (Login) *</label>
                    <input type="email" name="admin_email" value="{{ old('admin_email') }}" required
                           class="form-input-text" placeholder="admin@taller.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Contraseña Temporal *</label>
                    <input type="password" name="admin_password" value="password123" required
                           class="form-input-text" placeholder="••••••••">
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; margin-bottom: 40px;">
            <a href="{{ route('superadmin.talleres.index') }}" class="btn btn-outline" style="padding: 12px 24px;">
                Cancelar
            </a>
            <button type="submit" class="btn btn-amber" style="padding: 12px 32px; font-size: 14px;">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Crear Empresa y Asignar Licencia</span>
            </button>
        </div>
    </form>

</div>

<script>
    function seleccionarPlan(dias, estado, elemento) {
        document.querySelectorAll('.plan-card').forEach(function(card) {
            card.style.borderColor = 'var(--border)';
            card.style.boxShadow = 'none';
        });

        elemento.style.borderColor = '#d97706';
        elemento.style.boxShadow = '0 0 0 2px rgba(217, 119, 6, 0.2)';

        document.getElementById('inputEstado').value = estado;

        var hoy = new Date();
        hoy.setDate(hoy.getDate() + dias);
        var mes = ('0' + (hoy.getMonth() + 1)).slice(-2);
        var dia = ('0' + hoy.getDate()).slice(-2);
        var fechaStr = hoy.getFullYear() + '-' + mes + '-' + dia;

        document.getElementById('inputVencimiento').value = fechaStr;
    }
</script>
@endsection
