@if ($paginator->hasPages())
<nav class="pagination-bar" role="navigation" aria-label="Navigasi halaman">
    <span class="pagination-info">
        Menampilkan {{ $paginator->firstItem() }} sampai {{ $paginator->lastItem() }} dari {{ number_format($paginator->total()) }} data
    </span>
    <ul class="pagination pagination-sm mb-0">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled"><span class="page-link">&laquo; Previous</span></li>
        @else
            <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo; Previous</a></li>
        @endif

        {{-- Numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <li class="page-item disabled"><span class="page-link">{{ $element }}</span></li>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next &raquo;</a></li>
        @else
            <li class="page-item disabled"><span class="page-link">Next &raquo;</span></li>
        @endif
    </ul>
</nav>
@endif
