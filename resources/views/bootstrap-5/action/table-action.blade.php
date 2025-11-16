<a
    href="{{ $action }}"
    class="btn {{ $styles }}"
    role="button"
    @if($tooltip)
        data-bs-toggle="tooltip"
        data-bs-title="{{ $tooltip }}"
    @endif
>{{ $label }}</a>