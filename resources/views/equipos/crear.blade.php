@extends('layouts.app')

@section('titulo', 'Registrar Nuevo Equipo')

@section('contenido')
<div style="max-width: 860px; margin: 0 auto;" x-data="buscadorEquipo()">

    <!-- Encabezado -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Registrar Nuevo Dispositivo / Equipo</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Asocia un equipo a un cliente y configura sus ciclos de mantenimiento preventivo.</p>
        </div>

        <a href="{{ route('equipos.index') }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a Equipos</span>
        </a>
    </div>

    <form method="POST" action="{{ route('equipos.store') }}">
        @csrf

        <!-- 1. IDENTIFICACIÓN Y VINCULACIÓN -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-laptop-medical" style="color: #0284c7;"></i>
                        <span>1. Datos del Dispositivo y Propietario</span>
                    </h3>
                    <p>Busca al cliente titular por nombre, cédula / documento o teléfono en vivo.</p>
                </div>
            </div>

            <div class="form-grid-2">
                
                <!-- Buscador Inteligente en Tiempo Real de Clientes -->
                <div class="form-group form-group-full" style="position: relative;" @click.outside="mostrarDropdown = false">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">
                            <i class="fa-solid fa-magnifying-glass" style="color: #0284c7; margin-right: 4px;"></i> Cliente Propietario (Búsqueda por Nombre, Cédula / Documento o Teléfono) *
                        </label>
                        <a href="{{ route('clientes.create') }}" target="_blank" style="font-size: 11px; font-weight: 800; color: #0284c7; text-decoration: none;">+ Crear Cliente</a>
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
                                    Seleccionar
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

                    <input type="hidden" name="cliente_id" :value="clienteSeleccionado ? clienteSeleccionado.id : ''" required>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="margin-bottom: 0;">Categoría del Equipo *</label>
                        <a href="{{ route('categorias.index') }}" target="_blank" style="font-size: 11px; font-weight: 800; color: #0284c7; text-decoration: none;">+ Categorías</a>
                    </div>
                    <select name="categoria_id" id="categoria_id" required class="form-input-text">
                        <option value="">Seleccione categoría...</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Marca *</label>
                    <input type="text" name="marca" value="{{ old('marca') }}" required
                           class="form-input-text" placeholder="Ej. Dell, Samsung, Whirlpool, LG, Apple...">
                </div>

                <div class="form-group">
                    <label class="form-label">Modelo *</label>
                    <input type="text" name="modelo" value="{{ old('modelo') }}" required
                           class="form-input-text" placeholder="Ej. Inspiron 15 3520, No-Frost 420L, iPhone 13...">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Número de Serie / IMEI / Placa de Identificación</label>
                    <input type="text" name="numero_serie" value="{{ old('numero_serie') }}"
                           class="form-input-text" placeholder="Ej. CN-0K7G2H-70166-99B-01AB / 358920114092831">
                </div>

            </div>
        </div>

        <!-- 2. ESTADO FÍSICO Y CONDICIONES INICIALES -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-clipboard-check" style="color: #0284c7;"></i>
                        <span>2. Estado Físico y Notas Iniciales</span>
                    </h3>
                    <p>Detalles estéticos y observaciones del dispositivo al momento del registro.</p>
                </div>
            </div>

            <div class="form-group form-group-full">
                <label class="form-label">Observaciones Físicas / Estéticas</label>
                <textarea name="observaciones_fisicas" rows="3" 
                          class="form-textarea" 
                          placeholder="Ej. Tapa con rayones superficiales, falta tornillo inferior izquierdo, display intacto...">{{ old('observaciones_fisicas') }}</textarea>
            </div>
        </div>

        <!-- 3. CICLOS DE MANTENIMIENTO PREVENTIVO -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-clock-rotate-left" style="color: #d97706;"></i>
                        <span>3. Programación de Mantenimiento Preventivo</span>
                    </h3>
                    <p>Define las fechas para que el sistema genere alertas automáticas de servicio preventivo.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Fecha de Último Servicio / Mantenimiento</label>
                    <input type="date" name="fecha_ultimo_servicio" value="{{ old('fecha_ultimo_servicio', date('Y-m-d')) }}" class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha del Próximo Mantenimiento (Opcional)</label>
                    <input type="date" name="fecha_proximo_mantenimiento" value="{{ old('fecha_proximo_mantenimiento') }}" class="form-input-text">
                    <span style="font-size: 11px; color: #64748b; margin-top: 4px; display: block;">
                        * Si lo dejas en blanco, se calculará automáticamente según el intervalo de la categoría.
                    </span>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; margin-bottom: 40px;">
            <a href="{{ route('equipos.index') }}" class="btn btn-outline" style="padding: 12px 24px;">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 14px;">
                <i class="fa-solid fa-plus"></i>
                <span>Registrar Equipo</span>
            </button>
        </div>

    </form>

</div>

<script>
    function buscadorEquipo() {
        return {
            query: '',
            mostrarDropdown: false,
            clienteSeleccionado: null,
            todosClientes: @json($clientes),
            resultados: [],

            init() {
                this.resultados = this.todosClientes.slice(0, 8);

                @php 
                    $preId = old('cliente_id', $clienteSeleccionadoId ?? request('cliente_id'));
                @endphp
                @if(!empty($preId))
                    var cliId = {{ $preId }};
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
            },

            limpiarCliente() {
                this.clienteSeleccionado = null;
                this.query = '';
                this.resultados = this.todosClientes.slice(0, 8);
                this.mostrarDropdown = true;
            }
        };
    }
</script>
@endsection
