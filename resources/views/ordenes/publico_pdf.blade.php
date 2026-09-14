@extends('layouts.invitado')

@section('titulo', 'Informe de Servicio #' . $orden->codigo_orden)

@section('contenido')
<div class="w-full max-w-2xl bg-white rounded-3xl shadow-xl border border-slate-200/80 overflow-hidden my-4" x-data="{ fotoModal: null }">
    
    <!-- Encabezado del Taller -->
    <div class="bg-gradient-to-r from-slate-900 to-sky-900 text-white p-6 sm:p-8">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <span class="text-xs uppercase tracking-widest text-sky-400 font-bold">Informe Técnico Digital</span>
                <h1 class="text-2xl font-black mt-1">{{ $orden->taller?->nombre_comercial ?? 'ServiGest' }}</h1>
                <p class="text-xs text-slate-300">{{ $orden->taller?->telefono }} | {{ $orden->taller?->direccion }}</p>
            </div>

            <div class="text-right">
                <span class="text-xs text-slate-300 block">Orden de Servicio</span>
                <span class="text-lg font-black text-sky-400 font-mono">{{ $orden->codigo_orden }}</span>
            </div>
        </div>
    </div>

    <!-- Contenido -->
    <div class="p-6 sm:p-8 space-y-6">
        
        <!-- Estado y Botón de Descarga PDF -->
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs text-slate-500 font-bold uppercase block">Estado del Servicio</span>
                @php
                    $colores = [
                        'pendiente' => 'text-amber-700 bg-amber-100 border-amber-300',
                        'en_proceso' => 'text-sky-700 bg-sky-100 border-sky-300',
                        'finalizado' => 'text-emerald-700 bg-emerald-100 border-emerald-300',
                        'entregado' => 'text-slate-700 bg-slate-200 border-slate-300',
                    ];
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black border mt-1 {{ $colores[$orden->estado] ?? 'text-slate-700 bg-slate-100' }}">
                    <i class="fa-solid fa-circle text-[8px]"></i>
                    <span>{{ strtoupper(str_replace('_', ' ', $orden->estado)) }}</span>
                </span>
            </div>

            <a href="{{ route('ordenes.pdf.descargar_publico', ['token' => $orden->token_publico_pdf]) }}" target="_blank"
               class="inline-flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold px-5 py-3 rounded-xl shadow-lg shadow-rose-600/30 transition">
                <i class="fa-solid fa-file-pdf text-base"></i>
                <span>Descargar Informe en PDF</span>
            </a>
        </div>

        <!-- Datos del Cliente y Dispositivo -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-xs text-slate-400 font-bold uppercase">Cliente Titular</p>
                <p class="font-bold text-slate-800 mt-1">{{ $orden->cliente?->nombre_completo }}</p>
                <p class="text-xs text-slate-500">{{ $orden->cliente?->telefono }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <p class="text-xs text-slate-400 font-bold uppercase">Equipo en Atención</p>
                <p class="font-bold text-slate-800 mt-1">{{ $orden->equipo?->marca }} {{ $orden->equipo?->modelo }}</p>
                <p class="text-xs text-slate-500">Serie: {{ $orden->equipo?->numero_serie ?? 'No registrada' }}</p>
            </div>
        </div>

        <!-- Problema y Trabajo Realizado -->
        <div class="space-y-4">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Falla Reportada Inicialmente</h3>
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700">
                    {{ $orden->problema_reportado }}
                </div>
            </div>

            @if($orden->diagnostico)
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Diagnóstico Técnico</h3>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700">
                        {{ $orden->diagnostico }}
                    </div>
                </div>
            @endif

            @if($orden->procedimiento_realizado)
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Trabajo Realizado / Solución</h3>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700">
                        {{ $orden->procedimiento_realizado }}
                    </div>
                </div>
            @endif

            @if($orden->repuestos_usados)
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Repuestos Utilizados</h3>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700">
                        {{ $orden->repuestos_usados }}
                    </div>
                </div>
            @endif
        </div>

        <!-- Total Liquidado -->
        <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-900">Total Liquidación del Servicio</span>
            <span class="text-2xl font-black text-emerald-700">${{ number_format($orden->costo_total, 0, ',', '.') }}</span>
        </div>

        <!-- Evidencias Fotográficas -->
        @if($orden->evidencias->isNotEmpty())
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Evidencias Fotográficas del Servicio (Toca para ampliar)</h3>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($orden->evidencias as $evidencia)
                        <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 flex flex-col cursor-pointer shadow-sm hover:shadow transition"
                             @click="fotoModal = { url: '{{ $evidencia->url_imagen }}', etiqueta: '{{ $evidencia->nombre_etiqueta }}', desc: '{{ addslashes($evidencia->descripcion ?? '') }}' }">
                            <div class="relative h-32 bg-slate-900 overflow-hidden">
                                <img src="{{ $evidencia->url_imagen }}" alt="Foto" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/20 flex items-center justify-center opacity-0 hover:opacity-100 transition text-white text-xs font-bold gap-1">
                                    🔍 Ampliar
                                </div>
                            </div>
                            <div class="p-2">
                                <span class="text-[10.5px] font-bold text-sky-700 block">{{ $evidencia->nombre_etiqueta }}</span>
                                @if($evidencia->descripcion)
                                    <span class="text-[11px] text-slate-600 line-clamp-2">{{ $evidencia->descripcion }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Firma Digital si existe -->
        @if($orden->ruta_firma_cliente)
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-700 block">Comprobante de Entrega y Firma</span>
                    <span class="text-xs text-slate-500">Firmado por: {{ $orden->nombre_firmante ?? $orden->cliente?->nombre_completo }}</span>
                </div>
                <img src="{{ asset('storage/' . $orden->ruta_firma_cliente) }}" alt="Firma" class="h-12 bg-white border border-slate-200 rounded p-1">
            </div>
        @endif

    </div>

    <!-- Modal Visor para Móvil / Cliente -->
    <div x-show="fotoModal !== null" 
         x-cloak 
         @keydown.escape.window="fotoModal = null"
         class="fixed inset-0 z-50 bg-black/90 backdrop-blur-sm flex flex-col items-center justify-center p-4"
         @click.self="fotoModal = null">
        
        <button type="button" @click="fotoModal = null" class="absolute top-4 right-4 text-white bg-white/20 hover:bg-white/30 rounded-full w-10 h-10 flex items-center justify-center text-lg font-bold">
            ✕
        </button>

        <div class="max-w-xl w-full flex flex-col items-center">
            <img :src="fotoModal?.url" class="max-h-[75vh] w-auto object-contain rounded-2xl shadow-2xl border border-white/20 mb-3" alt="Foto Ampliada">
            
            <div class="bg-slate-900/90 border border-white/10 text-white rounded-2xl p-4 w-full text-center">
                <span class="text-xs font-bold text-sky-400 block" x-text="fotoModal?.etiqueta"></span>
                <p class="text-xs text-slate-200 mt-1" x-text="fotoModal?.desc || 'Foto de evidencia del servicio'"></p>
            </div>
        </div>
    </div>

    <!-- Pie -->
    <div class="bg-slate-100 p-4 text-center text-xs text-slate-400 border-t border-slate-200">
        Sistema de Seguimiento Técnico • Desarrollado con <strong>ServiGest SaaS</strong>
    </div>

</div>
@endsection
