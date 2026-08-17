<td class="{{ $styles }}">
    <div class="d-flex align-items-center w-100 {{ $cellStyles }}">
        @include('eloquent-tables::bootstrap-5.table.column-type.' . $type->getTdView(), [
            'value' => $value,
        ])
    </div>
</td>
