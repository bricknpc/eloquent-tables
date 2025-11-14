<div class="d-flex justify-content-between align-items-center">
    @if($sortable)
        <a href="{{ $href }}" class="d-flex justify-content-between align-items-center">
            <span>{{ $label }}</span>
            @if (!$isSorted)
                <x-bi-sort-alpha-down class="text-muted" />
            @else
                @if($sortDirection === \BrickNPC\EloquentTables\Enums\Sort::Asc)
                    <x-bi-sort-alpha-up />
                @else
                    <x-bi-sort-alpha-up />
                @endif
            @endif
        </a>
    @else
        {{ $label }}
    @endif
</div>