<th class="{{ $styles }}">
    @if($sortable)
        <a href="{{ $href }}" class="d-flex align-items-center w-100 text-decoration-none text-light {{ $cellStyles !== '' ? $cellStyles : 'justify-content-between' }}">
            @include('eloquent-tables::bootstrap-5.table.column-type.' . $type->getThView(), ['value' => $label])
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
        <div class="d-flex align-items-center w-100 {{ $cellStyles !== '' ? $cellStyles : 'justify-content-between' }}">
            @include('eloquent-tables::bootstrap-5.table.column-type.' . $type->getThView(), ['value' => $label])
        </div>
    @endif
</th>
