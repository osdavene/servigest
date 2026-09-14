@extends('layouts.app')

@section('titulo', 'Configuración de Mi Taller')

@section('contenido')
<div style="max-width: 960px; margin: 0 auto;">

    <!-- Encabezado -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Configuración y Datos de tu Taller</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Personaliza la información de tu empresa, el logo para los informes PDF y tus políticas de garantía.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('configuracion.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- 1. IDENTIDAD VISUAL & LOGO DEL TALLER -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-image" style="color: #0284c7;"></i>
                        <span>1. Logo del Taller (Encabezado de Informes PDF y WhatsApp)</span>
                    </h3>
                    <p>Sube el logo o isotipo de tu negocio para que aparezca en los reportes técnicos entregados a tus clientes.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 200px 1fr; gap: 24px; align-items: center;">
                
                <!-- Vista previa del logo actual -->
                <div style="text-align: center;">
                    <div style="width: 180px; height: 140px; border: 2px dashed var(--border); border-radius: 16px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; padding: 10px; margin: 0 auto;">
                        @if($taller->url_logo)
                            <img src="{{ $taller->url_logo }}" id="vistaPreviaLogo" alt="Logo de {{ $taller->nombre_comercial }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        @else
                            <div id="sinLogoIcono" style="text-align: center; color: #94a3b8;">
                                <i class="fa-solid fa-building-circle-arrow-right" style="font-size: 36px; margin-bottom: 6px; display: block;"></i>
                                <span style="font-size: 11px; font-weight: 700;">Sin logo subido</span>
                            </div>
                            <img src="" id="vistaPreviaLogo" alt="Vista previa" style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;">
                        @endif
                    </div>
                </div>

                <!-- Input de archivo -->
                <div>
                    <label class="form-label">Seleccionar archivo de imagen (PNG, JPG, WEBP o SVG):</label>
                    <input type="file" name="logo" id="inputLogoArchivo" accept="image/*" onchange="mostrarVistaPrevia(this)" class="form-input-text" style="padding: 10px; background: #ffffff;">
                    <span style="font-size: 11.5px; color: #64748b; display: block; margin-top: 6px;">
                        Recomendación: Fondo transparente (PNG), formato horizontal o cuadrado, tamaño máximo 3 MB.
                    </span>
                </div>

            </div>
        </div>

        <!-- 2. DATOS COMERCIALES Y DE CONTACTO -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-store" style="color: #10b981;"></i>
                        <span>2. Información Comercial y Canales de Atención</span>
                    </h3>
                    <p>Estos datos se imprimirán en la cabecera de las órdenes de servicio técnico.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group form-group-full">
                    <label class="form-label">Nombre Comercial del Taller *</label>
                    <input type="text" name="nombre_comercial" value="{{ old('nombre_comercial', $taller->nombre_comercial) }}" required
                           class="form-input-text" placeholder="Ej. ElectroTech Soluciones Integrales">
                </div>

                <div class="form-group">
                    <label class="form-label">Identificación Tributaria / NIT / RUT</label>
                    <input type="text" name="identificacion_fiscal" value="{{ old('identificacion_fiscal', $taller->identificacion_fiscal) }}"
                           class="form-input-text" placeholder="Ej. NIT 900.123.456-7">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono de Contacto (WhatsApp Oficial) *</label>
                    <input type="text" name="telefono" value="{{ old('telefono', $taller->telefono) }}" required
                           class="form-input-text" placeholder="Ej. 573105554433">
                </div>

                <div class="form-group">
                    <label class="form-label">Correo Electrónico de Notificaciones *</label>
                    <input type="email" name="email" value="{{ old('email', $taller->email) }}" required
                           class="form-input-text" placeholder="contacto@taller.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Ciudad / Municipio</label>
                    <input type="text" name="ciudad" value="{{ old('ciudad', $taller->ciudad) }}"
                           class="form-input-text" placeholder="Ej. Bogotá, Medellín, Cali...">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Dirección Física del Establecimiento</label>
                    <input type="text" name="direccion" value="{{ old('direccion', $taller->direccion) }}"
                           class="form-input-text" placeholder="Ej. Carrera 15 # 85-30 Local 102">
                </div>
            </div>
        </div>

        <!-- 3. PERSONALIZACIÓN DE ÓRDENES E INFORMES PDF -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-file-invoice" style="color: #8b5cf6;"></i>
                        <span>3. Personalización de Órdenes y Políticas de Garantía</span>
                    </h3>
                    <p>Configura el prefijo de tus consecutivos y el texto legal que leerán tus clientes al recibir su equipo.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Prefijo Consecutivo para las Órdenes</label>
                    <input type="text" name="prefijo_orden" value="{{ old('prefijo_orden', $taller->prefijo_orden ?? 'OT') }}" maxlength="10"
                           class="form-input-text" placeholder="Ej. OT, SERV, ORD, TECH">
                    <span style="font-size: 11px; color: #94a3b8; display: block; margin-top: 4px;">Ejemplo de formato resultante: <strong>{{ $taller->prefijo_orden ?? 'OT' }}-00001</strong></span>
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Texto de Garantía y Términos del Servicio (Pie del Informe PDF)</label>
                    <textarea name="texto_garantia" rows="3" class="form-textarea" placeholder="Ej. Garantía de 90 días sobre mano de obra y repuestos instalados. Todo equipo no reclamado después de 60 días causará costos de bodegaje...">{{ old('texto_garantia', $taller->texto_garantia) }}</textarea>
                </div>
            </div>
        </div>

        <!-- 4. ESTADO DE LICENCIA Y SUSCRIPCIÓN (Solo Lectura Informativo) -->
        <!-- 4. INTEGRACIÓN Y NOTIFICACIONES AUTOMÁTICAS DE WHATSAPP -->
        <div class="card" x-data="{ autoWhatsapp: {{ $taller->whatsapp_auto_notify_enabled ? 'true' : 'false' }}, provider: '{{ $taller->whatsapp_api_provider ?: 'webhook_personalizado' }}' }">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-brands fa-whatsapp" style="color: #10b981; font-size: 20px;"></i>
                        <span>4. Notificaciones Automáticas por WhatsApp</span>
                    </h3>
                    <p>Envía mensajes automáticos al cliente cuando se crea una orden o cambia a En Proceso, Finalizado o Entregado.</p>
                </div>
                <div>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; font-weight: bold; color: #0f172a;">
                        <input type="checkbox" name="whatsapp_auto_notify_enabled" value="1" x-model="autoWhatsapp" style="width: 18px; height: 18px; accent-color: #10b981;">
                        <span>Activar Envío Automático</span>
                    </label>
                </div>
            </div>

            <div x-show="autoWhatsapp" x-transition style="margin-top: 16px;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label">Proveedor / Método de Envío</label>
                        <select name="whatsapp_api_provider" x-model="provider" class="form-input-text">
                            <option value="webhook_personalizado">Webhook Personalizado (Make / n8n / Zapier / Evolution API)</option>
                            <option value="whatsapp_cloud_api">WhatsApp Cloud API Oficial (Meta / Facebook)</option>
                            <option value="ultramsg">UltraMsg API / ChatAPI</option>
                        </select>
                    </div>

                    <div class="form-group" x-show="provider === 'webhook_personalizado'">
                        <label class="form-label">URL del Webhook (POST)</label>
                        <input type="url" name="whatsapp_webhook_url" value="{{ old('whatsapp_webhook_url', $taller->whatsapp_webhook_url) }}" class="form-input-text" placeholder="https://tu-webhook-n8n-o-make.com/webhook/servigest">
                    </div>

                    <div class="form-group" x-show="provider !== 'webhook_personalizado'">
                        <label class="form-label">Phone Number ID / Instance ID</label>
                        <input type="text" name="whatsapp_phone_number_id" value="{{ old('whatsapp_phone_number_id', $taller->whatsapp_phone_number_id) }}" class="form-input-text" placeholder="Ej. 109876543210987 o instance12345">
                    </div>

                    <div class="form-group form-group-full" x-show="provider !== 'webhook_personalizado'">
                        <label class="form-label">API Token / Bearer Token</label>
                        <input type="password" name="whatsapp_api_token" value="{{ old('whatsapp_api_token', $taller->whatsapp_api_token) }}" class="form-input-text" placeholder="EAAXxxxx...">
                    </div>
                </div>

                <div style="margin-top: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #166534;">
                    <i class="fa-solid fa-circle-info"></i> <strong>Variables disponibles en las plantillas:</strong> 
                    <code>{cliente}</code>, <code>{codigo_orden}</code>, <code>{equipo}</code>, <code>{estado}</code>, <code>{total}</code>, <code>{enlace_seguimiento}</code>, <code>{taller}</code>.
                </div>

                <div class="form-grid-2" style="margin-top: 16px;">
                    <div class="form-group">
                        <label class="form-label">Plantilla: Al Registrar Orden (Pendiente)</label>
                        <textarea name="whatsapp_template_creada" rows="2" class="form-input-text" placeholder="Hola {cliente}, su equipo {equipo} ingresó a {taller}. Orden #{codigo_orden}. Seguimiento: {enlace_seguimiento}">{{ old('whatsapp_template_creada', $taller->whatsapp_template_creada) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Plantilla: Al Pasar a En Proceso</label>
                        <textarea name="whatsapp_template_en_proceso" rows="2" class="form-input-text" placeholder="Hola {cliente}, su equipo {equipo} ya está en revisión técnica. Orden #{codigo_orden}. Detalles: {enlace_seguimiento}">{{ old('whatsapp_template_en_proceso', $taller->whatsapp_template_en_proceso) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Plantilla: Al Finalizar Reparación (Listo para Entrega)</label>
                        <textarea name="whatsapp_template_finalizada" rows="2" class="form-input-text" placeholder="¡Buenas noticias {cliente}! Su equipo {equipo} está listo. Total: ${total}. Ver informe: {enlace_seguimiento}">{{ old('whatsapp_template_finalizada', $taller->whatsapp_template_finalizada) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Plantilla: Al Entregar Equipo (Cierre)</label>
                        <textarea name="whatsapp_template_entregada" rows="2" class="form-input-text" placeholder="Hola {cliente}, su orden #{codigo_orden} ha sido entregada. Gracias por confiar en {taller}. Comprobante: {enlace_seguimiento}">{{ old('whatsapp_template_entregada', $taller->whatsapp_template_entregada) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. ESTADO DE LA LICENCIA -->
        <div class="card" style="background: #f8fafc; border-color: var(--border);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                <div>
                    <span class="badge {{ $taller->estado_suscripcion === 'activo' ? 'badge-active' : 'badge-pending' }}">
                        {{ $taller->estado_suscripcion === 'activo' ? 'Licencia Activa' : 'Periodo de Prueba' }}
                    </span>
                    <h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-top: 6px;">
                        Plan Contratado: {{ $taller->planLicencia?->nombre ?? 'Plan Estándar' }}
                    </h4>
                    <p style="font-size: 12px; color: #64748b; margin-top: 2px;">
                        Vigencia hasta el: <strong>{{ $taller->fecha_vencimiento_suscripcion?->format('d/m/Y') ?? 'Indefinido' }}</strong>
                    </p>
                </div>

                <div style="font-size: 12px; color: #94a3b8; text-align: right;">
                    <span>Para renovaciones o ampliación de planes, contacta al soporte del SaaS.</span>
                </div>
            </div>
        </div>

        <!-- Botón Guardar -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; margin-bottom: 40px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 14px;">
                <i class="fa-solid fa-circle-check"></i>
                <span>Guardar y Actualizar Configuración</span>
            </button>
        </div>
    </form>

</div>

<script>
    function mostrarVistaPrevia(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.getElementById('vistaPreviaLogo');
                var icono = document.getElementById('sinLogoIcono');
                if (icono) icono.style.display = 'none';
                img.src = e.target.result;
                img.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
