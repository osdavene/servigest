@extends('layouts.app')

@section('titulo', 'Editar Equipo')

@section('contenido')
<div style="max-width: 860px; margin: 0 auto;">

    <!-- Encabezado -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div>
            <h3 style="font-size: 20px; font-weight: 900; color: #0f172a;">Editar Dispositivo: {{ $equipo->marca }} {{ $equipo->modelo }}</h3>
            <p style="font-size: 13px; color: #64748b; margin-top: 2px;">Actualiza los datos técnicos o reprograma el mantenimiento preventivo.</p>
        </div>

        <a href="{{ route('equipos.show', $equipo) }}" class="btn btn-outline">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Volver a Hoja de Vida</span>
        </a>
    </div>

    <form method="POST" action="{{ route('equipos.update', $equipo) }}">
        @csrf
        @method('PUT')

        <!-- 1. IDENTIFICACIÓN Y VINCULACIÓN -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-laptop-medical" style="color: #0284c7;"></i>
                        <span>1. Datos del Dispositivo y Propietario</span>
                    </h3>
                    <p>Información técnica de marca, modelo y cliente titular.</p>
                </div>
            </div>

            <div class="form-grid-2">
                
                <div class="form-group">
                    <label class="form-label">Cliente Propietario *</label>
                    <select name="cliente_id" id="cliente_id" required class="form-input-text">
                        @foreach($clientes as $cli)
                            <option value="{{ $cli->id }}" {{ old('cliente_id', $equipo->cliente_id) == $cli->id ? 'selected' : '' }}>
                                {{ $cli->nombre_completo }} ({{ $cli->telefono }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Categoría del Equipo *</label>
                    <select name="categoria_id" id="categoria_id" required class="form-input-text">
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ old('categoria_id', $equipo->categoria_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Marca *</label>
                    <input type="text" name="marca" value="{{ old('marca', $equipo->marca) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Modelo *</label>
                    <input type="text" name="modelo" value="{{ old('modelo', $equipo->modelo) }}" required
                           class="form-input-text">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Número de Serie / IMEI / Placa de Identificación</label>
                    <input type="text" name="numero_serie" value="{{ old('numero_serie', $equipo->numero_serie) }}"
                           class="form-input-text" style="font-family: monospace;">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Observaciones Físicas / Estado de Recepción</label>
                    <textarea name="observaciones_fisicas" rows="3" class="form-textarea">{{ old('observaciones_fisicas', $equipo->observaciones_fisicas) }}</textarea>
                </div>

            </div>
        </div>

        <!-- 2. CICLOS DE MANTENIMIENTO PREVENTIVO -->
        <div class="card" style="border-color: #fde68a; background: linear-gradient(180deg, #fffdfa 0%, #ffffff 100%);">
            <div class="card-header" style="border-bottom-color: #fde68a;">
                <div>
                    <h3 style="color: #92400e; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-calendar-check" style="color: #d97706;"></i>
                        <span>2. Alertas de Mantenimiento Preventivo</span>
                    </h3>
                    <p>Fechas de control periódico.</p>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Fecha del Último Servicio</label>
                    <input type="date" name="fecha_ultimo_servicio" value="{{ old('fecha_ultimo_servicio', $equipo->fecha_ultimo_servicio?->format('Y-m-d')) }}" class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Próximo Mantenimiento Sugerido</label>
                    <input type="date" name="fecha_proximo_mantenimiento" value="{{ old('fecha_proximo_mantenimiento', $equipo->fecha_proximo_mantenimiento?->format('Y-m-d')) }}" class="form-input-text">
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; margin-top: 24px; margin-bottom: 40px;">
            <a href="{{ route('equipos.show', $equipo) }}" class="btn btn-outline" style="padding: 12px 24px;">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 32px; font-size: 14px;">
                <i class="fa-solid fa-save"></i>
                <span>Guardar Cambios</span>
            </button>
        </div>

    </form>

</div>
@endsection
