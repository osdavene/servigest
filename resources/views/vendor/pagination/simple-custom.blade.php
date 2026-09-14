@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de Páginas" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; font-size: 13px; color: #64748b;">
        {{-- Botón Anterior --}}
        @if ($paginator->onFirstPage())
            <span style="display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 12px; background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 10px; cursor: not-allowed; font-weight: 600; gap: 6px;">
                <i class="fa-solid fa-chevron-left" style="font-size: 11px;"></i>
                <span>Anterior</span>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 12px; background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 10px; text-decoration: none; font-weight: 600; gap: 6px; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#0284c7'; this.style.color='#0284c7';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';">
                <i class="fa-solid fa-chevron-left" style="font-size: 11px;"></i>
                <span>Anterior</span>
            </a>
        @endif

        {{-- Botón Siguiente --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 12px; background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 10px; text-decoration: none; font-weight: 600; gap: 6px; transition: all 0.2s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#0284c7'; this.style.color='#0284c7';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1'; this.style.color='#0f172a';">
                <span>Siguiente</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
            </a>
        @else
            <span style="display: inline-flex; align-items: center; justify-content: center; height: 34px; padding: 0 12px; background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 10px; cursor: not-allowed; font-weight: 600; gap: 6px;">
                <span>Siguiente</span>
                <i class="fa-solid fa-chevron-right" style="font-size: 11px;"></i>
            </span>
        @endif
    </nav>
@endif
