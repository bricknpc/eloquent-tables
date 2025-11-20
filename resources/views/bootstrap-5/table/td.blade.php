<td class="{{ $styles }}">
    @include('eloquent-tables::bootstrap-5.table.column-type.' . $type->getTdView(), [
        'value' => $value,
        'cellStyles' => $cellStyles,
    ])
</td>