<div class="card-dashboard {{ $class ?? '' }}">
    <div class="card-body">
        @if (isset($eyebrow))
            <p class="page-eyebrow">{{ $eyebrow }}</p>
        @endif
        @if (isset($title))
            <h2 class="card-title {{ isset($eyebrow) ? '' : '' }}">{{ $title }}</h2>
        @endif
        {{ $slot }}
    </div>
</div>
