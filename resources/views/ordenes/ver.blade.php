@extends('layouts.app')

@section('titulo', 'Orden de Servicio ' . $orden->codigo_orden)

@section('contenido')
<div x-data="{ modalEvidencia: false, fotoModal: null }">

    <!-- Encabezado Principal de la Orden y Botones Rápidos -->
    <div class="card" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            
            <div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h2 style="font-size: 24px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">{{ $orden->codigo_orden }}</h2>
                    
                    @if($orden->estado === 'finalizado')
                        <span class="badge badge-active"><i class="fa-solid fa-circle-check"></i> Finalizado</span>
                    @elseif($orden->estado === 'en_proceso')
                        <span class="badge badge-process"><i class="fa-solid fa-screwdriver-wrench"></i> En Proceso</span>
                    @elseif($orden->estado === 'entregado')
                        <span class="badge badge-slate"><i class="fa-solid fa-box-open"></i> Entregado</span>
                    @elseif($orden->estado === 'cancelado')
                        <span class="badge badge-danger"><i class="fa-solid fa-ban"></i> Cancelado</span>
                    @else
                        <span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Pendiente</span>
                    @endif

                    @if($orden->tipo_ubicacion === 'servicio_en_domicilio')
                        <span class="badge badge-purple">
                            <i class="fa-solid fa-house"></i> En Domicilio
                        </span>
                    @else
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; border-color: #bae6fd;">
                            <i class="fa-solid fa-shop"></i> En Taller
                        </span>
                    @endif
                </div>

                <p style="font-size: 12.5px; color: #64748b; margin-top: 6px;">
                    Fecha de Ingreso: <strong>{{ $orden->fecha_ingreso?->format('d/m/Y h:i A') }}</strong>
                    @if($orden->fecha_finalizacion)
                        <span style="margin: 0 6px;">•</span>
                        Finalizado: <strong style="color: #10b981;">{{ $orden->fecha_finalizacion->format('d/m/Y h:i A') }}</strong>
                    @endif
                </p>
            </div>

            <!-- Acciones: WhatsApp, Descargar PDF, Editar/Firmar -->
            <div style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
                
                <a href="{{ $orden->enlace_whatsapp_reporte }}" target="_blank" class="btn btn-emerald" style="box-shadow: 0 6px 14px rgba(16, 185, 129, 0.3);">
                    <i class="fa-brands fa-whatsapp" style="font-size: 16px;"></i>
                    <span>Enviar a WhatsApp</span>
                </a>

                <a href="{{ route('ordenes.pdf.descargar', $orden) }}" target="_blank" class="btn btn-rose" style="box-shadow: 0 6px 14px rgba(239, 68, 68, 0.3);">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Descargar PDF</span>
                </a>

                @if($orden->estado !== 'finalizado' && $orden->estado !== 'entregado')
                    <a href="{{ route('ordenes.edit', ['orden' => $orden, 'accion' => 'cerrar']) }}" class="btn btn-primary" style="box-shadow: 0 6px 14px rgba(2, 132, 199, 0.35);">
                        <i class="fa-solid fa-check-double"></i>
                        <span>Finalizar / Entregar Orden</span>
                    </a>
                @endif

                <a href="{{ route('ordenes.edit', $orden) }}" class="btn btn-dark">
                    <i class="fa-solid fa-pen-to-square"></i>
                    <span>Editar / Firmar</span>
                </a>

            </div>

        </div>
    </div>

    <!-- Layout Grid de 2 Columnas Responsivo -->
    <div class="detail-grid-layout">
        
        <!-- COLUMNA IZQUIERDA: CLIENTE, EQUIPO Y TÉCNICO -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Tarjeta de Cliente -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-header" style="padding-bottom: 12px; margin-bottom: 14px;">
                    <div>
                        <h3 style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                            <i class="fa-solid fa-user" style="color: #0284c7; margin-right: 6px;"></i> Datos del Cliente
                        </h3>
                    </div>
                </div>

                <strong style="font-size: 16px; color: #0f172a; display: block;">{{ $orden->cliente?->nombre_completo }}</strong>
                <span style="font-size: 12px; color: #64748b; font-family: monospace;">{{ $orden->cliente?->identificacion ?? 'Sin documento' }}</span>

                <div style="margin-top: 14px; display: flex; flex-direction: column; gap: 8px; font-size: 12.5px;">
                    <div style="display: flex; align-items: center; gap: 8px; color: #334155;">
                        <i class="fa-solid fa-phone" style="color: #94a3b8; width: 16px;"></i>
                        <span style="font-weight: 700;">{{ $orden->cliente?->telefono }}</span>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 8px; color: #334155;">
                        <i class="fa-solid fa-location-dot" style="color: #ef4444; width: 16px; margin-top: 2px;"></i>
                        <span>{{ $orden->cliente?->direccion }}{{ $orden->cliente?->barrio ? ', ' . $orden->cliente?->barrio : '' }} ({{ $orden->cliente?->ciudad }})</span>
                    </div>
                </div>

                <div style="margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--border); display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                    <a href="{{ $orden->cliente?->enlace_whatsapp }}" target="_blank" class="btn btn-outline" style="font-size: 11px; padding: 6px; color: #10b981; border-color: #a7f3d0; background: #ecfdf5;">
                        <i class="fa-brands fa-whatsapp"></i> Chat Directo
                    </a>
                    <a href="{{ $orden->cliente?->enlace_google_maps }}" target="_blank" class="btn btn-outline" style="font-size: 11px; padding: 6px; color: #0284c7; border-color: #bae6fd; background: #f0f9ff;">
                        <i class="fa-solid fa-map-location-dot"></i> Ubicación
                    </a>
                </div>
            </div>

            <!-- Tarjeta de Equipo -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-header" style="padding-bottom: 12px; margin-bottom: 14px;">
                    <div>
                        <h3 style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                            <i class="fa-solid fa-laptop-medical" style="color: #8b5cf6; margin-right: 6px;"></i> Dispositivo en Servicio
                        </h3>
                    </div>
                </div>

                <span class="badge" style="background: #f5f3ff; color: #7c3aed; border-color: #ddd6fe; font-size: 10px;">
                    {{ $orden->equipo?->categoria?->nombre ?? 'Equipo' }}
                </span>

                <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 8px;">
                    {{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}
                </strong>

                <p style="font-size: 12px; color: #64748b; font-family: monospace; margin-top: 4px;">
                    N° Serie: {{ $orden->equipo?->numero_serie ?? 'N/A' }}
                </p>

                @if($orden->equipo?->observaciones_fisicas)
                    <div style="margin-top: 12px; padding: 10px; background: #f8fafc; border: 1px solid var(--border); border-radius: 10px; font-size: 11.5px; color: #475569;">
                        <strong style="color: #0f172a; display: block; margin-bottom: 2px;">Estado físico al recibir:</strong>
                        {{ $orden->equipo->observaciones_fisicas }}
                    </div>
                @endif
            </div>

            <!-- Técnico Asignado -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-header" style="padding-bottom: 10px; margin-bottom: 10px;">
                    <h3 style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                        <i class="fa-solid fa-user-gear" style="color: #10b981; margin-right: 6px;"></i> Técnico a Cargo
                    </h3>
                </div>
                <strong style="font-size: 14px; color: #0f172a;">{{ $orden->tecnico?->nombre_completo ?? 'Sin asignar' }}</strong>
                <span style="font-size: 12px; color: #64748b; display: block; margin-top: 2px;">{{ $orden->tecnico?->email ?? 'N/A' }}</span>
            </div>

        </div>

        <!-- COLUMNA DERECHA: DIAGNÓSTICO, PROCEDIMIENTO, LIQUIDACIÓN, FIRMA Y FOTOS -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            
            <!-- Detalles Técnicos del Trabajo -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-header">
                    <div>
                        <h3 style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-clipboard-check" style="color: #0284c7;"></i>
                            <span>Detalles de la Intervención Técnica</span>
                        </h3>
                        <p>Diagnóstico, procedimientos ejecutados y repuestos aplicados.</p>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">
                            Problema Reportado por el Cliente:
                        </span>
                        <div style="padding: 12px 14px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; font-size: 13px; color: #0f172a; line-height: 1.5;">
                            {{ $orden->problema_reportado }}
                        </div>
                    </div>

                    <div>
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">
                            Diagnóstico del Especialista:
                        </span>
                        <div style="padding: 12px 14px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; font-size: 13px; color: #0f172a; line-height: 1.5;">
                            {{ $orden->diagnostico ?? 'Sin diagnóstico registrado aún.' }}
                        </div>
                    </div>

                    <div>
                        <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">
                            Procedimiento y Trabajo Realizado:
                        </span>
                        <div style="padding: 12px 14px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; font-size: 13px; color: #0f172a; line-height: 1.5;">
                            {{ $orden->procedimiento_realizado ?? 'En proceso de ejecución.' }}
                        </div>
                    </div>

                    @if($orden->repuestos_usados)
                        <div>
                            <span style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #64748b; display: block; margin-bottom: 4px;">
                                Repuestos y Materiales Utilizados:
                            </span>
                            <div style="padding: 12px 14px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 12px; font-size: 13px; color: #0f172a; line-height: 1.5;">
                                {{ $orden->repuestos_usados }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Liquidación Económica -->
                <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px;">
                    <div style="padding: 14px; background: #f8fafc; border-radius: 14px; text-align: center; border: 1px solid var(--border);">
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748b;">Mano de Obra</span>
                        <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 4px;">${{ number_format($orden->costo_mano_obra, 0, ',', '.') }}</strong>
                    </div>

                    <div style="padding: 14px; background: #f8fafc; border-radius: 14px; text-align: center; border: 1px solid var(--border);">
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #64748b;">Repuestos</span>
                        <strong style="font-size: 16px; color: #0f172a; display: block; margin-top: 4px;">${{ number_format($orden->costo_repuestos, 0, ',', '.') }}</strong>
                    </div>

                    <div style="padding: 14px; background: #ecfdf5; border-radius: 14px; text-align: center; border: 1.5px solid #a7f3d0;">
                        <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #065f46;">Total Servicio</span>
                        <strong style="font-size: 20px; color: #047857; display: block; margin-top: 2px;">${{ number_format($orden->costo_total, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <!-- Firma Digital Registrada -->
                @if($orden->ruta_firma_cliente)
                    <div style="margin-top: 20px; padding: 16px; background: #f0fdf4; border: 1.5px solid #bbf7d0; border-radius: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                        <div>
                            <span style="font-size: 12px; font-weight: 800; color: #166534; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-circle-check"></i> Orden Firmada a Conformidad por el Cliente
                            </span>
                            <p style="font-size: 12px; color: #374151; margin-top: 4px;">Recibido por: <strong>{{ $orden->nombre_firmante ?? $orden->cliente?->nombre_completo }}</strong></p>
                            <span style="font-size: 11px; color: #6b7280;">Fecha: {{ $orden->fecha_firma?->format('d/m/Y h:i A') }}</span>
                        </div>

                        <img src="{{ asset('storage/' . $orden->ruta_firma_cliente) }}" alt="Firma Cliente" style="max-height: 60px; background: #ffffff; border: 1px solid var(--border); border-radius: 8px; padding: 4px;">
                    </div>
                @endif
            </div>

            <!-- Galería de Evidencias Fotográficas -->
            <div class="card" style="margin-bottom: 0;">
                <div class="card-header">
                    <div>
                        <h3 style="display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-camera" style="color: #0284c7;"></i>
                            <span>Evidencias Fotográficas ({{ $orden->evidencias->count() }})</span>
                        </h3>
                        <p>Fotos antes, durante, fallas en componentes y estado final entregado.</p>
                    </div>

                    <button @click="modalEvidencia = true" class="btn btn-primary" style="font-size: 12px; padding: 8px 16px;">
                        <i class="fa-solid fa-camera"></i>
                        <span>Subir Fotografía</span>
                    </button>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 16px;">
                    @forelse($orden->evidencias as $evidencia)
                        <div style="border: 1px solid var(--border); border-radius: 14px; overflow: hidden; background: #ffffff; display: flex; flex-direction: column; box-shadow: var(--card-shadow);">
                            
                            <!-- Foto clickeable para ver en grande -->
                            <div style="position: relative; cursor: pointer; overflow: hidden; height: 145px; background: #0f172a;"
                                 @click="fotoModal = { url: '{{ $evidencia->url_imagen }}', etiqueta: '{{ $evidencia->nombre_etiqueta }}', desc: '{{ addslashes($evidencia->descripcion ?? '') }}', fecha: '{{ $evidencia->created_at?->format('d/m/Y h:i A') }}' }">
                                <img src="{{ $evidencia->url_imagen }}" alt="Evidencia" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.08)'" onmouseout="this.style.transform='scale(1)'">
                                
                                <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.35); opacity: 0; transition: opacity 0.2s; display: flex; align-items: center; justify-content: center; color: #ffffff; font-size: 12px; font-weight: 800; gap: 6px;" 
                                     onmouseover="this.style.opacity='1'" 
                                     onmouseout="this.style.opacity='0'">
                                    <i class="fa-solid fa-magnifying-glass-plus"></i> Ver en Grande
                                </div>
                            </div>
                            
                            <div style="padding: 10px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <span class="badge" style="font-size: 10px; background: {{ str_contains($evidencia->etiqueta, 'recibe') || str_contains($evidencia->etiqueta, 'antes') ? '#fef3c7; color: #b45309; border-color: #fde68a;' : (str_contains($evidencia->etiqueta, 'devuelve') || str_contains($evidencia->etiqueta, 'despues') ? '#ecfdf5; color: #047857; border-color: #a7f3d0;' : '#e0f2fe; color: #0369a1; border-color: #bae6fd;') }}">
                                        {{ $evidencia->nombre_etiqueta }}
                                    </span>
                                    @if($evidencia->descripcion)
                                        <p style="font-size: 11.5px; color: #475569; margin-top: 6px; line-height: 1.3;">{{ $evidencia->descripcion }}</p>
                                    @endif
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 6px; border-top: 1px solid var(--border);">
                                    <button type="button" 
                                            @click="fotoModal = { url: '{{ $evidencia->url_imagen }}', etiqueta: '{{ $evidencia->nombre_etiqueta }}', desc: '{{ addslashes($evidencia->descripcion ?? '') }}', fecha: '{{ $evidencia->created_at?->format('d/m/Y h:i A') }}' }" 
                                            style="background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 4px;">
                                        <i class="fa-solid fa-magnifying-glass-plus"></i> Ampliar
                                    </button>

                                    <form action="{{ route('evidencias.destroy', $evidencia) }}" method="POST" onsubmit="return confirm('¿Eliminar esta fotografía?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 11px; font-weight: 700; cursor: pointer;">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 36px 16px; color: #94a3b8;">
                            <i class="fa-solid fa-images" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                            <p style="font-size: 13px;">No se han subido evidencias fotográficas aún.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- Modal Subir Evidencia Fotográfica -->
    <div x-show="modalEvidencia" x-cloak style="position: fixed; inset: 0; z-index: 100; background: rgba(11, 17, 32, 0.7); display: flex; align-items: center; justify-content: center; padding: 20px;">
        <div class="card" style="max-width: 520px; width: 100%; margin-bottom: 0; box-shadow: var(--modal-shadow);" @click.outside="modalEvidencia = false">
            <div class="card-header" style="margin-bottom: 16px;">
                <h3 style="font-size: 16px;">Subir Evidencia Fotográfica</h3>
                <button @click="modalEvidencia = false" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('evidencias.store', $orden) }}" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Seleccionar o Tomar Fotografía *</label>
                    <input type="file" name="foto" required accept="image/*" class="form-input-text" style="padding: 8px; background: #ffffff;">
                    <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">⚡ Se optimizará automáticamente sin perder nitidez.</span>
                </div>

                <div class="form-group">
                    <label class="form-label">Etapa / Clasificación de la Foto *</label>
                    <select name="etiqueta" required class="form-input-text">
                        <option value="como_se_recibe">📥 Cómo se recibe el equipo (Estado físico / Rayones / Daño inicial)</option>
                        <option value="falla_detectada">🔍 Falla encontrada / Diagnóstico técnico (Componente dañado)</option>
                        <option value="durante_reparacion">🛠️ Durante la reparación (Repuesto nuevo / Procedimiento)</option>
                        <option value="como_se_devuelve">📤 Cómo se devuelve el equipo (Reparado / Funcionando / Entregado)</option>
                        <option value="otra">📎 Otra evidencia técnica</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción / Hallazgo</label>
                    <input type="text" name="descripcion" placeholder="Ej. Condensador hinchado en fuente secundaria" class="form-input-text">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border);">
                    <button type="button" @click="modalEvidencia = false" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir Fotografía</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Visor de Imágenes en Alta Resolución (Lightbox) -->
    <div x-show="fotoModal !== null" 
         x-cloak 
         @keydown.escape.window="fotoModal = null"
         style="position: fixed; inset: 0; z-index: 120; background: rgba(11, 17, 32, 0.92); backdrop-filter: blur(8px); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px;"
         @click.self="fotoModal = null">
        
        <!-- Botón Cerrar Flotante -->
        <button type="button" @click="fotoModal = null" style="position: absolute; top: 20px; right: 24px; background: rgba(255,255,255,0.15); border: none; color: #ffffff; width: 44px; height: 44px; border-radius: 50%; font-size: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div style="max-width: 960px; width: 100%; display: flex; flex-direction: column; align-items: center;">
            
            <!-- Imagen en Alta Definición -->
            <div style="position: relative; max-height: 75vh; width: 100%; display: flex; justify-content: center; align-items: center; margin-bottom: 16px;">
                <img :src="fotoModal?.url" alt="Fotografía en Grande" style="max-height: 75vh; max-width: 100%; object-fit: contain; border-radius: 12px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7); border: 2px solid rgba(255,255,255,0.2);">
            </div>

            <!-- Panel Inferior de Información y Descarga -->
            <div style="background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255,255,255,0.15); border-radius: 14px; padding: 14px 20px; width: 100%; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; color: #ffffff;">
                <div>
                    <span style="font-size: 13px; font-weight: 800; color: #38bdf8; display: block;" x-text="fotoModal?.etiqueta"></span>
                    <p style="font-size: 13px; color: #e2e8f0; margin-top: 2px;" x-text="fotoModal?.desc || 'Sin observaciones adicionales'"></p>
                    <span style="font-size: 11px; color: #94a3b8;" x-show="fotoModal?.fecha" x-text="'Capturada el: ' + fotoModal?.fecha"></span>
                </div>

                <div style="display: flex; gap: 10px;">
                    <a :href="fotoModal?.url" target="_blank" class="btn btn-outline" style="color: #ffffff; border-color: rgba(255,255,255,0.3); font-size: 12px; padding: 8px 14px; background: rgba(255,255,255,0.08);">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        <span>Abrir Original</span>
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
