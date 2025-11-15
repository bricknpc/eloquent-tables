<th>
    <div class="d-flex justify-content-between align-items-center">
        @if($sortable)
            <a href="{{ $href }}" class="d-flex justify-content-between align-items-center w-100 text-decoration-none text-light">
                <span>{{ $label }}</span>
                @if (!$isSorted)
                    <span class="text-muted">{{ $iconNone }}</span>
                @else
                    @if($sortDirection === \BrickNPC\EloquentTables\Enums\Sort::Asc)
                        {{ $iconDesc }}
                    @else
                        {{ $iconAsc }}
                    @endif
                @endif
            </a>
        @else
            {{ $label }}
        @endif
    </div>
</th>