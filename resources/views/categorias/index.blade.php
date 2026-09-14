@extends('layouts.app')

@section('titulo', 'Categorías de Equipos')

@section('contenido')
<div x-data="moduloCategorias()">

    <!-- Barra de Filtros y Búsqueda Inteligente -->
    <div class="filter-bar" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px; flex: 1; flex-wrap: wrap;">
            <div class="search-input-wrapper" style="min-width: 280px; flex: 1; max-width: 460px;">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" 
                       x-model="busqueda"
                       placeholder="🔍 Escribe para buscar categoría (Laptops, Neveras...)" 
                       class="input-control"
                       autofocus>
            </div>

            <button type="button" x-show="busqueda.length > 0" @click="busqueda = ''" class="btn btn-outline btn-icon" title="Limpiar búsqueda" x-cloak>
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <button @click="modalCrear = true" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            <span>Nueva Categoría</span>
        </button>
    </div>

    <!-- Grid de Tarjetas de Categorías con Filtrado en Tiempo Real -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        @forelse($categorias as $cat)
            <div class="card tarjeta-categoria" 
                 x-show="coincide('{{ strtolower($cat->nombre) }}', '{{ strtolower($cat->descripcion ?? '') }}')"
                 style="display: flex; flex-direction: column; justify-content: space-between; margin-bottom: 0; transition: all 0.2s;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                        <div style="width: 44px; height: 44px; background: #f0f9ff; color: #0284c7; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                            <i class="fa-solid fa-tags"></i>
                        </div>
                        <span class="badge badge-slate" style="font-size: 11px;">
                            {{ $cat->equipos_count }} {{ Str::plural('Equipo', $cat->equipos_count) }}
                        </span>
                    </div>

                    <strong style="font-size: 16px; color: #0f172a; display: block;">{{ $cat->nombre }}</strong>
                    <p style="font-size: 12.5px; color: #64748b; margin-top: 6px; line-height: 1.5;">{{ $cat->descripcion ?? 'Sin descripción adicional.' }}</p>

                    @if($cat->requiere_mantenimiento_preventivo)
                        <div style="margin-top: 14px; padding: 8px 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; font-size: 11.5px; font-weight: 700; color: #b45309; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                            <span>Mantenimiento cada {{ $cat->intervalo_mantenimiento_dias }} días</span>
                        </div>
                    @endif
                </div>

                <div style="margin-top: 20px; padding-top: 14px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <a href="{{ route('equipos.index', ['categoria_id' => $cat->id]) }}" style="font-size: 12px; font-weight: 700; color: #0284c7; text-decoration: none;">
                        Ver Equipos ({{ $cat->equipos_count }}) →
                    </a>

                    <form action="{{ route('categorias.destroy', $cat) }}" method="POST" class="inline" onsubmit="return confirm('¿Está seguro de eliminar esta categoría?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline" style="color: #ef4444; font-size: 11px; padding: 6px 12px;">
                            <i class="fa-solid fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 48px 20px;">
                <i class="fa-solid fa-tags" style="font-size: 36px; color: #94a3b8; margin-bottom: 12px; display: block;"></i>
                <h4 style="font-size: 16px; font-weight: 800; color: #334155;">No hay categorías registradas</h4>
                <p style="font-size: 13px; color: #94a3b8; margin-top: 4px;">Crea tus primeras categorías para organizar los dispositivos atendidos.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Crear Categoría -->
    <div x-show="modalCrear" x-cloak style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 99; display: flex; align-items: center; justify-content: center; padding: 20px;">
        <div class="card" style="max-width: 480px; width: 100%; box-shadow: var(--modal-shadow); margin-bottom: 0;" @click.outside="modalCrear = false">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                <h3 style="font-size: 16px; font-weight: 900; color: #0f172a;">Nueva Categoría de Equipos</h3>
                <button @click="modalCrear = false" style="background: none; border: none; font-size: 18px; color: #94a3b8; cursor: pointer;">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('categorias.store') }}" x-data="{ preventivo: false }">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nombre de la Categoría *</label>
                    <input type="text" name="nombre" required placeholder="Ej. Laptops, Neveras, Motos, Smart TVs..." class="form-input-text">
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" rows="2" placeholder="Breve descripción del tipo de equipos atendidos..." class="form-textarea"></textarea>
                </div>

                <div style="background: #f8fafc; border: 1px solid var(--border); border-radius: 12px; padding: 14px; margin-bottom: 18px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #334155; cursor: pointer;">
                        <input type="checkbox" name="requiere_mantenimiento_preventivo" value="1" x-model="preventivo" style="accent-color: var(--primary);">
                        <span>Habilitar Mantenimiento Preventivo Periódico</span>
                    </label>

                    <div x-show="preventivo" style="margin-top: 12px;" x-cloak>
                        <label style="font-size: 11px; font-weight: 700; color: #64748b; display: block; margin-bottom: 4px;">Intervalo en días (ej. 180 = 6 meses)</label>
                        <input type="number" name="intervalo_mantenimiento_dias" min="1" placeholder="180" class="form-input-text" style="padding: 8px 12px;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" @click="modalCrear = false" class="btn btn-outline">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function moduloCategorias() {
        return {
            busqueda: '{{ $busqueda ?? '' }}',
            modalCrear: false,

            coincide(nombre, descripcion) {
                if (!this.busqueda || this.busqueda.trim() === '') return true;
                var term = this.busqueda.toLowerCase().trim();
                return nombre.includes(term) || descripcion.includes(term);
            }
        };
    }
</script>
@endsection
