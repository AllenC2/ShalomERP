@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de páginas">
        <ul class="pagination-minimal">
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <li class="page-item-minimal disabled" aria-disabled="true">
                    <span class="page-link-minimal">
                        <i class="bi bi-chevron-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item-minimal">
                    <a class="page-link-minimal" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>
            @endif

            {{-- Números de Página --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item-minimal disabled" aria-disabled="true">
                        <span class="page-link-minimal">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item-minimal active" aria-current="page">
                                <span class="page-link-minimal">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item-minimal">
                                <a class="page-link-minimal" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <li class="page-item-minimal">
                    <a class="page-link-minimal" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item-minimal disabled" aria-disabled="true">
                    <span class="page-link-minimal">
                        <i class="bi bi-chevron-right"></i>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
