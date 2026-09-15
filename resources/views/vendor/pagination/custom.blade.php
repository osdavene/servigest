@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de Páginas" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px; font-size: 13px; color: var(--text-muted, #64748b);">
        
        <!-- Información de registros mostrados en Español -->
        <div style="font-weight: 500;">
            Mostrando 
            <span style="font-weight: 700; color: var(--text-main, #0f172a);">{{ $paginator->firstItem() ?? 0 }}</span>
            a 
            <span style="font-weight: 700; color: var(--text-main, #0f172a);">{{ $paginator->lastItem() ?? 0 }}</span>
            de 
            <span style="font-weight: 700; color: var(--text-main, #0f172a);">{{ $paginator->total() }}</span>
            resultados
        </div>

        <!-- Botones de Paginación -->
        <div style="display: inline-flex; align-items: center; gap: 4px;">
            
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="btn-pagination-disabled">
                    <i class="fa-solid fa-chevron-left" style="font-size: 11px;"></i>
                    <span>Anterior</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn-pagination">
                    <i class="fa-solid fa-chevron-left" style="font-size: 11px;"></i>
                    <span>Anterior</span>
                </a>
            @endif

            {{-- Elementos / Números de Página --}}
            @foreach ($elements as $element)
                {{-- Separador de 3 puntos "..." --}}
                @if (is_string($element))
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; color: var(--text-muted, #94a3b8); font-weight: 700;">{{ $element }}</span>
                @endif

                {{-- Array de Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn-pagination-active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="btn-pagination">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn-pagination">
                    <span>Siguiente</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
                </a>
            @else
                <span class="btn-pagination-disabled">
                    <span>Siguiente</span>
                    <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
                </span>
            @endif

        </div>
    </nav>
@endif
