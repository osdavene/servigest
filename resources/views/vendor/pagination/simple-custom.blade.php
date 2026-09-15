@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de Páginas" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; font-size: 13px; color: var(--text-muted, #64748b);">
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
    </nav>
@endif
