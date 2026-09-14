@extends('layouts.app')

@section('titulo', 'Crear Nueva Orden de Trabajo')

@section('contenido')
<div style="max-width: 900px; margin: 0 auto;" x-data="buscadorOrden()">

    <!-- Encabezado -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Apertura de Orden de Trabajo</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Recepción técnica de dispositivo, captura de falla inicial y asignación de técnico.</p>
        </div>

        <a href="{{ route('ordenes.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a Órdenes</span>
        </a>
    </div>

    <form method="POST" action="{{ route('ordenes.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- 1. VINCULACIÓN DE CLIENTE Y DISPOSITIVO -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-user-tag" style="color: #0284c7;"></i>
                        <span>1. Cliente y Dispositivo a Reparar</span>
                    </h3>
                    <p>Busca al cliente por nombre, cédula / documento o teléfono en tiempo real.</p>
                </div>
            </div>

            <div class="form-grid-2">
                
                <!-- Buscador Inteligente en Tiempo Real de Clientes -->
                <div class="form-group form-group-full" style="position: relative;" @click.outside="mostrarDropdown = false">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">
                            <i class="fa-solid fa-magnifying-glass" style="color: #0284c7; margin-right: 4px;"></i> Buscar Cliente (Nombre, Cédula / Documento o Teléfono) *
                        </label>
                        <a href="{{ route('clientes.create') }}" target="_blank" style="font-size: 11px; font-weight: 800; color: #0284c7; text-decoration: none;">+ Registrar Nuevo Cliente</a>
                    </div>

                    <!-- Input de Búsqueda Reactiva -->
                    <div style="position: relative;">
                        <input type="text" 
                               x-model="query" 
                               @input="filtrarClientes()" 
                               @focus="abrirDropdown()"
                               placeholder="🔍 Escribe nombre, CC / Cédula, teléfono..." 
                               class="form-input-text"
                               style="font-size: 14px; padding-right: 40px; border-color: #0284c7; background: #ffffff;">
                        
                        <button type="button" x-show="query.length > 0" @click="limpiarCliente()" style="position: absolute; right: 12px; top: 12px; background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 15px;" x-cloak>
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Dropdown de Resultados de Búsqueda en Vivo -->
                    <div x-show="mostrarDropdown && resultados.length > 0" 
                         x-cloak
                         style="position: absolute; top: 100%; left: 0; right: 0; z-index: 50; background: #ffffff; border: 1.5px solid #0284c7; border-radius: 12px; box-shadow: var(--modal-shadow); margin-top: 4px; max-height: 250px; overflow-y: auto;">
                        <template x-for="cli in resultados" :key="cli.id">
                            <div @click="seleccionarCliente(cli)" 
                                 style="padding: 10px 14px; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.15s; display: flex; align-items: center; justify-content: space-between;"
                                 onmouseover="this.style.background='#f0f9ff'" 
                                 onmouseout="this.style.background='#ffffff'">
                                <div>
                                    <strong style="font-size: 13.5px; color: #0f172a;" x-text="cli.nombre_completo"></strong>
                                    <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                        <span x-show="cli.identificacion" style="background: #f1f5f9; padding: 1px 6px; border-radius: 4px; font-family: monospace; font-weight: 700; margin-right: 6px;" x-text="'CC: ' + cli.identificacion"></span>
                                        <span style="color: #334155;"><i class="fa-solid fa-phone" style="color: #94a3b8;"></i> <span x-text="cli.telefono"></span></span>
                                        <span x-show="cli.ciudad" style="margin-left: 6px; color: #94a3b8;" x-text="'(' + cli.ciudad + ')'"></span>
                                    </div>
                                </div>
                                <span style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 4px 8px; border-radius: 6px;">
                                    <span x-text="cli.equipos ? cli.equipos.length : 0"></span> Equipos
                                </span>
                            </div>
                        </template>
                    </div>

                    <!-- Mensaje si no hay resultados -->
                    <div x-show="mostrarDropdown && resultados.length === 0 && query.trim().length > 0" 
                         x-cloak
                         style="position: absolute; top: 100%; left: 0; right: 0; z-index: 50; background: #ffffff; border: 1.5px solid var(--border); border-radius: 12px; padding: 14px; text-align: center; color: #64748b; font-size: 12.5px; box-shadow: var(--modal-shadow); margin-top: 4px;">
                        No se encontró ningún cliente con "<strong><span x-text="query"></span></strong>".
                        <a href="{{ route('clientes.create') }}" target="_blank" style="color: #0284c7; font-weight: 800; display: block; margin-top: 4px;">+ Registrar este Cliente</a>
                    </div>

                    <!-- Tarjeta de Cliente Seleccionado Activo -->
                    <div x-show="clienteSeleccionado" x-cloak style="margin-top: 10px; padding: 12px 16px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center; font-weight: 900;">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div>
                                <strong style="color: #166534; font-size: 14px;" x-text="clienteSeleccionado?.nombre_completo"></strong>
                                <span style="font-size: 12px; color: #374151; display: block;">
                                    Doc: <span style="font-family: monospace; font-weight: 700;" x-text="clienteSeleccionado?.identificacion || 'Sin documento'"></span> • Tel: <span x-text="clienteSeleccionado?.telefono"></span> • <span x-text="clienteSeleccionado?.ciudad || ''"></span>
                                </span>
                            </div>
                        </div>

                        <button type="button" @click="limpiarCliente()" class="btn btn-outline" style="font-size: 11px; padding: 4px 8px; color: #dc2626;">
                            Cambiar
                        </button>
                    </div>

                    <!-- Input Oculto obligatorio con el ID del Cliente -->
                    <input type="hidden" name="cliente_id" :value="clienteSeleccionado ? clienteSeleccionado.id : ''" required>
                </div>

                <!-- Selección de Equipo del Cliente -->
                <div class="form-group form-group-full">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">Dispositivo / Equipo a Reparar *</label>
                        <a :href="'{{ route('equipos.create') }}' + (clienteSeleccionado ? '?cliente_id=' + clienteSeleccionado.id : '')" 
                           target="_blank" 
                           style="font-size: 11px; font-weight: 800; color: #0284c7; text-decoration: none;">
                            + Registrar Nuevo Equipo para este Cliente
                        </a>
                    </div>
                    
                    <select name="equipo_id" id="equipo_id" required class="form-input-text">
                        <option value="">-- Seleccione el equipo a intervenir --</option>
                        
                        <!-- Equipos filtrados dinámicamente según el cliente seleccionado -->
                        <template x-for="eq in equiposDisponibles" :key="eq.id">
                            <option :value="eq.id" 
                                    :selected="eq.id == {{ $equipoId ?? 'null' }}"
                                    x-text="eq.marca + ' ' + eq.modelo + ' (Serie: ' + (eq.numero_serie || 'N/A') + ')'">
                            </option>
                        </template>

                        <!-- Fallback si aún no selecciona cliente -->
                        <template x-if="!clienteSeleccionado || equiposDisponibles.length === 0">
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}" 
                                        class="equipo-option-base"
                                        {{ (old('equipo_id') == $equipo->id || (isset($equipoId) && $equipoId == $equipo->id)) ? 'selected' : '' }}>
                                    {{ $equipo->marca }} {{ $equipo->modelo }} (Serie: {{ $equipo->numero_serie ?? 'N/A' }}) - Propietario: {{ $equipo->cliente?->nombre_completo }}
                                </option>
                            @endforeach
                        </template>
                    </select>

                    <p x-show="clienteSeleccionado && equiposDisponibles.length === 0" x-cloak style="font-size: 11.5px; color: #dc2626; margin-top: 6px; font-weight: 700;">
                        ⚠️ Este cliente aún no tiene equipos registrados. 
                        <a :href="'{{ route('equipos.create') }}?cliente_id=' + clienteSeleccionado.id" target="_blank" style="color: #0284c7; text-decoration: underline;">Haz clic aquí para registrar su primer equipo</a>.
                    </p>
                </div>

            </div>
        </div>

        <!-- 2. DIAGNÓSTICO TÉCNICO INICIAL Y ASIGNACIÓN -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-wrench" style="color: #0284c7;"></i>
                        <span>2. Falla Reportada, Ubicación y Asignación Técnica</span>
                    </h3>
                    <p>Detalla el motivo del servicio y el técnico responsable.</p>
                </div>
            </div>

            <div class="form-grid-2">
                
                <div class="form-group form-group-full">
                    <label class="form-label">Falla o Problema Reportado por el Cliente *</label>
                    <textarea name="problema_reportado" rows="3" required placeholder="Describe detalladamente el daño o servicio solicitado por el cliente..." class="form-textarea">{{ old('problema_reportado') }}</textarea>
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Accesorios o Elementos Dejados</label>
                    <input type="text" name="accesorios_incluidos" value="{{ old('accesorios_incluidos') }}" placeholder="Ej. Cargador original, cable HDMI, control remoto, estuche..." class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Técnico Asignado</label>
                    <select name="tecnico_asignado_id" class="form-input-text">
                        <option value="">-- Sin técnico asignado (Pendiente) --</option>
                        @foreach($tecnicos as $tec)
                            <option value="{{ $tec->id }}" {{ (old('tecnico_asignado_id', auth()->id()) == $tec->id) ? 'selected' : '' }}>
                                {{ $tec->nombre_completo }} ({{ ucfirst($tec->rol) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Modalidad / Ubicación del Servicio *</label>
                    <select name="tipo_ubicacion" required class="form-input-text">
                        <option value="ingresado_al_taller" {{ old('tipo_ubicacion') == 'ingresado_al_taller' ? 'selected' : '' }}>🏢 En las Instalaciones del Taller</option>
                        <option value="servicio_en_domicilio" {{ old('tipo_ubicacion') == 'servicio_en_domicilio' ? 'selected' : '' }}>🏠 Visita / Servicio en Domicilio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha Estimada de Promesa / Entrega</label>
                    <input type="datetime-local" name="fecha_estimada_entrega" value="{{ old('fecha_estimada_entrega') }}" class="form-input-text">
                </div>

            </div>
        </div>

        <!-- 3. LIQUIDACIÓN ECONÓMICA ESTIMADA / INICIAL -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-file-invoice-dollar" style="color: #0284c7;"></i>
                        <span>3. Cotización Inicial / Costos (Opcional)</span>
                    </h3>
                    <p>Puedes ingresar los valores estimados o dejarlos en $0 para liquidar al finalizar el servicio.</p>
                </div>
            </div>

            <div class="form-grid-2">
                
                <div class="form-group">
                    <label class="form-label">Mano de Obra ($)</label>
                    <input type="number" step="0.01" min="0" name="costo_mano_obra" id="costoManoObra" value="{{ old('costo_mano_obra', 0) }}" oninput="calcularTotal()" class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Repuestos / Materiales ($)</label>
                    <input type="number" step="0.01" min="0" name="costo_repuestos" id="costoRepuestos" value="{{ old('costo_repuestos', 0) }}" oninput="calcularTotal()" class="form-input-text">
                </div>

                <div class="form-group form-group-full" style="padding: 16px; background: #f8fafc; border: 1.5px solid var(--border); border-radius: 14px; display: flex; align-items: center; justify-content: space-between;">
                    <span style="font-size: 13px; font-weight: 800; color: #475569; text-transform: uppercase;">Total Estimado del Servicio:</span>
                    <strong style="font-size: 22px; font-weight: 900; color: #047857;" id="textoTotalServicio">$0</strong>
                </div>

            </div>
        </div>

        <!-- 4. FOTO DE RECEPCIÓN INICIAL -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-camera" style="color: #0284c7;"></i>
                        <span>4. Evidencia Fotográfica Inicial (Opcional)</span>
                    </h3>
                    <p>Captura el estado físico del equipo al momento de recibirlo (pantalla rayada, golpes, sellos rotos).</p>
                </div>
            </div>

            <div class="form-group">
                <input type="file" name="foto_inicial" accept="image/*" class="form-input-text" style="padding: 8px 12px; background: #ffffff;">
                <p style="font-size: 11px; color: #64748b; margin-top: 6px;">
                    ⚡ La imagen será comprimida automáticamente sin perder nitidez estilo WhatsApp.
                </p>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-bottom: 40px;">
            <a href="{{ route('ordenes.index') }}" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 28px; font-size: 14px;">
                <i class="fa-solid fa-check"></i>
                <span>Crear Orden de Trabajo</span>
            </button>
        </div>

    </form>

</div>

<script>
    function buscadorOrden() {
        return {
            query: '',
            mostrarDropdown: false,
            clienteSeleccionado: null,
            equiposDisponibles: [],
            todosClientes: @json($clientes),
            resultados: [],

            init() {
                // Preparar resultados iniciales
                this.resultados = this.todosClientes.slice(0, 8);

                @if(!empty($clienteId))
                    var cliId = {{ $clienteId }};
                    var cliEncontrado = this.todosClientes.find(c => c.id == cliId);
                    if (cliEncontrado) {
                        this.seleccionarCliente(cliEncontrado);
                    }
                @endif
            },

            abrirDropdown() {
                this.filtrarClientes();
                this.mostrarDropdown = true;
            },

            filtrarClientes() {
                this.mostrarDropdown = true;
                if (!this.query || this.query.trim() === '') {
                    this.resultados = this.todosClientes.slice(0, 8);
                    return;
                }

                var q = this.query.toLowerCase().trim();
                this.resultados = this.todosClientes.filter(c => {
                    var nombre = (c.nombre_completo || '').toLowerCase();
                    var doc = (c.identificacion || '').toLowerCase();
                    var tel = (c.telefono || '').toLowerCase();
                    var tel2 = (c.telefono_secundario || '').toLowerCase();
                    var ciudad = (c.ciudad || '').toLowerCase();

                    return nombre.includes(q) || doc.includes(q) || tel.includes(q) || tel2.includes(q) || ciudad.includes(q);
                }).slice(0, 12);
            },

            seleccionarCliente(cliente) {
                this.clienteSeleccionado = cliente;
                this.query = cliente.nombre_completo;
                this.mostrarDropdown = false;
                this.equiposDisponibles = cliente.equipos || [];

                // Seleccionar primer equipo si tiene
                this.$nextTick(() => {
                    var selectEq = document.getElementById('equipo_id');
                    if (selectEq && this.equiposDisponibles.length > 0) {
                        selectEq.value = this.equiposDisponibles[0].id;
                    }
                });
            },

            limpiarCliente() {
                this.clienteSeleccionado = null;
                this.query = '';
                this.equiposDisponibles = [];
                this.resultados = this.todosClientes.slice(0, 8);
                this.mostrarDropdown = true;
            }
        };
    }

    function calcularTotal() {
        var manoObra = parseFloat(document.getElementById('costoManoObra').value) || 0;
        var repuestos = parseFloat(document.getElementById('costoRepuestos').value) || 0;
        var total = manoObra + repuestos;
        document.getElementById('textoTotalServicio').innerText = '$' + total.toLocaleString('es-CO');
    }
</script>
@endsection
