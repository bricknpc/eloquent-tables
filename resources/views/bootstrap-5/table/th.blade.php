<th>
    <div class="d-flex justify-content-between align-items-center">
        @if($sortable)
            <a href="{{ $href }}" class="d-flex {{ !empty($cellStylesFlex) ? $cellStylesFlex : 'justify-content-between align-items-center' }} w-100 text-decoration-none text-light">
                @include('eloquent-tables::bootstrap-5.table.column-type.' . $type->getThView(), [
                    'value' => $label,
                    'styles' => $cellStyles
                ])
                @if (!$isSorted)
                    <span class="text-muted ms-2">{{ $iconNone }}</span>
                @else
                    <span class="ms-2">
                        @if($sortDirection === \BrickNPC\EloquentTables\Enums\Sort::Asc)
                            {{ $iconDesc }}
                        @else
                            {{ $iconAsc }}
                        @endif
                    </span>
                @endif
            </a>
        @else
            <div class="{{ $cellStyles }}">{{ $label }}</div>
        @endif
    </div>
</th>