@extends('layouts.app')

@section('titulo', 'Gestionar Orden: ' . $orden->codigo_orden)

@section('contenido')
<div style="max-width: 900px; margin: 0 auto;">

    <!-- Encabezado de la Orden -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">{{ $orden->codigo_orden }}</h3>
                <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 11px;">
                    {{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}
                </span>
            </div>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">
                Cliente: <strong style="color: #0f172a;">{{ $orden->cliente?->nombre_completo }}</strong> ({{ $orden->cliente?->telefono }})
            </p>
        </div>

        <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a la Orden</span>
        </a>
    </div>

    <form method="POST" action="{{ route('ordenes.update', $orden) }}" id="formularioOrden" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- 1. ESTADO Y TÉCNICO A CARGO -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-sliders" style="color: #0284c7;"></i>
                        <span>1. Estado y Ubicación del Servicio</span>
                    </h3>
                    <p>Actualiza la fase en la que se encuentra la orden y el especialista a cargo.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Estado de la Orden *</label>
                    <select name="estado" required class="form-input-text" style="font-weight: 700;">
                        @php
                            $estadoSeleccionado = old('estado', request('accion') === 'cerrar' ? 'finalizado' : $orden->estado);
                        @endphp
                        <option value="pendiente" {{ $estadoSeleccionado == 'pendiente' ? 'selected' : '' }}>🟡 Pendiente de Revisión</option>
                        <option value="en_proceso" {{ $estadoSeleccionado == 'en_proceso' ? 'selected' : '' }}>🔵 En Proceso de Diagnóstico / Reparación</option>
                        <option value="finalizado" {{ $estadoSeleccionado == 'finalizado' ? 'selected' : '' }}>🟢 Finalizado (Listo para Entrega)</option>
                        <option value="entregado" {{ $estadoSeleccionado == 'entregado' ? 'selected' : '' }}>⚪ Entregado al Cliente</option>
                        <option value="cancelado" {{ $estadoSeleccionado == 'cancelado' ? 'selected' : '' }}>🔴 Cancelado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Modalidad / Ubicación *</label>
                    <select name="tipo_ubicacion" required class="form-input-text">
                        <option value="ingresado_al_taller" {{ old('tipo_ubicacion', $orden->tipo_ubicacion) == 'ingresado_al_taller' ? 'selected' : '' }}>🏢 Ingresado al Taller</option>
                        <option value="servicio_en_domicilio" {{ old('tipo_ubicacion', $orden->tipo_ubicacion) == 'servicio_en_domicilio' ? 'selected' : '' }}>🏠 Servicio en Domicilio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Técnico Asignado</label>
                    <select name="tecnico_asignado_id" class="form-input-text">
                        <option value="">Sin asignar</option>
                        @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}" {{ old('tecnico_asignado_id', $orden->tecnico_asignado_id) == $tec->id ? 'selected' : '' }}>
                                {{ $tec->nombre_completo }} ({{ $tec->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha Promesa de Entrega</label>
                    <input type="date" name="fecha_promesa" value="{{ old('fecha_promesa', $orden->fecha_promesa?->format('Y-m-d')) }}" class="form-input-text">
                </div>
            </div>
        </div>

        <!-- 2. DIAGNÓSTICO Y TRABAJO TÉCNICO -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-screwdriver-wrench" style="color: #10b981;"></i>
                        <span>2. Diagnóstico Técnico y Procedimientos</span>
                    </h3>
                    <p>Información visible en el reporte PDF y en la consulta pública de WhatsApp.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group form-group-full">
                    <label class="form-label">Motivo de Ingreso / Falla Reportada *</label>
                    <textarea name="problema_reportado" rows="2" required class="form-textarea">{{ old('problema_reportado', $orden->problema_reportado) }}</textarea>
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Diagnóstico Técnico del Especialista</label>
                    <textarea name="diagnostico" rows="2" placeholder="Explica la falla encontrada tras abrir o revisar el dispositivo..." class="form-textarea">{{ old('diagnostico', $orden->diagnostico) }}</textarea>
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Procedimiento y Solución Aplicada</label>
                    <textarea name="procedimiento_realizado" rows="2" placeholder="Detalla el trabajo efectuado (mantenimiento, cambio de piezas, soldadura...)" class="form-textarea">{{ old('procedimiento_realizado', $orden->procedimiento_realizado) }}</textarea>
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Repuestos y Materiales Utilizados</label>
                    <input type="text" name="repuestos_usados" value="{{ old('repuestos_usados', $orden->repuestos_usados) }}" placeholder="Ej. Pantalla OLED original, Pasta térmica Artic MX-4, Capacitor 100uF..." class="form-input-text">
                </div>
            </div>
        </div>

        <!-- 3. LIQUIDACIÓN DE COSTOS -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-file-invoice-dollar" style="color: #059669;"></i>
                        <span>3. Liquidación Económica del Servicio</span>
                    </h3>
                    <p>Total a cobrar al cliente por mano de obra y piezas cambiadas.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Costo de Mano de Obra ($) *</label>
                    <input type="number" step="0.01" name="costo_mano_obra" id="costoManoObra" value="{{ old('costo_mano_obra', $orden->costo_mano_obra) }}"
                           oninput="calcularTotal()" class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Costo de Repuestos y Materiales ($) *</label>
                    <input type="number" step="0.01" name="costo_repuestos" id="costoRepuestos" value="{{ old('costo_repuestos', $orden->costo_repuestos) }}"
                           oninput="calcularTotal()" class="form-input-text">
                </div>

                <div class="form-group form-group-full">
                    <div style="padding: 16px; background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 14px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-weight: 800; font-size: 13px; color: #065f46; text-transform: uppercase;">Total Liquidado a Cobrar:</span>
                        <strong style="font-size: 24px; color: #047857;" id="textoTotalServicio">${{ number_format($orden->costo_total, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. FOTO DE CIERRE / CÓMO SE DEVUELVE EL EQUIPO (OPCIONAL AL FINALIZAR) -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-camera" style="color: #0284c7;"></i>
                        <span>4. Foto de Cierre / Cómo se devuelve el equipo (Opcional)</span>
                    </h3>
                    <p>Captura el equipo terminado, funcionando o limpio antes de entregarlo al cliente.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Fotografía de Entrega / Devolución</label>
                    <input type="file" name="foto_cierre" accept="image/*" class="form-input-text" style="padding: 8px; background: #ffffff;">
                    <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">⚡ Se comprimirá automáticamente tipo WhatsApp.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Observación de Cierre / Estado de Entrega</label>
                    <input type="text" name="descripcion_foto_cierre" placeholder="Ej. Equipo funcionando, pantalla probada y entregado con cargador" class="form-input-text">
                </div>
            </div>
        </div>

        <!-- 5. CAPTURA DE FIRMA DIGITAL (CANVAS HTML5) -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-signature" style="color: #8b5cf6;"></i>
                        <span>5. Firma Digital de Conformidad del Cliente</span>
                    </h3>
                    <p>Captura el trazo en pantalla táctil o mouse para certificar la entrega.</p>
                </div>

                @if($orden->ruta_firma_cliente)
                    <span class="badge badge-active"><i class="fa-solid fa-check"></i> Ya Firmada</span>
                @endif
            </div>

            <div class="form-grid-2">
                <div class="form-group form-group-full">
                    <label class="form-label">Nombre del Firmante / Quien Recibe</label>
                    <input type="text" name="nombre_firmante" value="{{ old('nombre_firmante', $orden->nombre_firmante ?? $orden->cliente?->nombre_completo) }}" class="form-input-text">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Área de Firma en Pantalla:</label>
                    <div style="border: 2px dashed #cbd5e1; border-radius: 14px; background: #ffffff; padding: 6px; text-align: center;">
                        <canvas id="lienzoFirma" width="600" height="180" style="width: 100%; height: 180px; touch-action: none; background: #ffffff; cursor: crosshair;"></canvas>
                        <input type="hidden" name="firma_canvas" id="firmaCanvasInput">
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                        <span style="font-size: 11px; color: #64748b;">Dibuja tu firma con el dedo o mouse en el recuadro superior.</span>
                        <button type="button" onclick="limpiarFirma()" class="btn btn-outline" style="font-size: 11px; padding: 4px 10px;">
                            <i class="fa-solid fa-eraser"></i> Limpiar Firma
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones Guardar -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; margin-bottom: 40px;">
            <a href="{{ route('ordenes.show', $orden) }}" class="btn btn-outline" style="padding: 12px 24px;">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 14px;">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Guardar y Actualizar Orden</span>
            </button>
        </div>

    </form>

</div>

<script>
    function calcularTotal() {
        var manoObra = parseFloat(document.getElementById('costoManoObra').value) || 0;
        var repuestos = parseFloat(document.getElementById('costoRepuestos').value) || 0;
        var total = manoObra + repuestos;
        document.getElementById('textoTotalServicio').innerText = '$' + total.toLocaleString('es-CO');
    }

    // Manejo de Canvas de Firma Digital
    var canvas = document.getElementById('lienzoFirma');
    var ctx = canvas.getContext('2d');
    var dibujando = false;
    var hayTrazo = false;

    ctx.strokeStyle = "#0f172a";
    ctx.lineWidth = 2.5;
    ctx.lineCap = "round";

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var clientX = e.clientX || (e.touches && e.touches[0].clientX);
        var clientY = e.clientY || (e.touches && e.touches[0].clientY);
        return {
            x: (clientX - rect.left) * (canvas.width / rect.width),
            y: (clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function empezarTrazo(e) {
        dibujando = true;
        hayTrazo = true;
        var pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function dibujar(e) {
        if (!dibujando) return;
        var pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function pararTrazo() {
        dibujando = false;
    }

    // Eventos Mouse
    canvas.addEventListener('mousedown', empezarTrazo);
    canvas.addEventListener('mousemove', dibujar);
    window.addEventListener('mouseup', pararTrazo);

    // Eventos Touch (Celulares/Tablets)
    canvas.addEventListener('touchstart', empezarTrazo);
    canvas.addEventListener('touchmove', dibujar);
    canvas.addEventListener('touchend', pararTrazo);

    function limpiarFirma() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hayTrazo = false;
        document.getElementById('firmaCanvasInput').value = '';
    }

    // Interceptar envío del formulario para guardar firma
    document.getElementById('formularioOrden').addEventListener('submit', function() {
        if (hayTrazo) {
            document.getElementById('firmaCanvasInput').value = canvas.toDataURL('image/png');
        }
    });
</script>
@endsection
