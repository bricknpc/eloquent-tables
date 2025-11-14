<div class="d-flex justify-content-between">
    @if($sortable)
        <span>{{ $label }}</span>
        <x-bi-sort-alpha-down class="text-muted" />
    @else
        {{ $label }}
    @endif
</div>