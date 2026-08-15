@php
    use Illuminate\Http\Request;
    use BrickNPC\EloquentTables\Column;
    use BrickNPC\EloquentTables\Columns\ColumnLabelRenderer;
    use BrickNPC\EloquentTables\Columns\ColumnValueRenderer;

    /** @var Request $request */
    /** @var Column[] $columns */
    /** @var ColumnLabelRenderer $columnLabelRenderer */
    /** @var ColumnValueRenderer $columnValueRenderer */
    /** @var ?string $bulkActionColumnWidth */
@endphp
<thead>
    <tr>
        @if($bulkActionCount > 0)
            <th class="text-center" @if($bulkActionColumnWidth !== null) style="width: {{ $bulkActionColumnWidth }};" @endif>
                <div class="form-check form-switch d-flex justify-content-center">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="bulk-action-switch-{{ $id }}"
                        data-{{ $dataNamespace }}-select-all="true"
                        aria-label="{{ __('Select all') }}"
                    >
                </div>
            </th>
        @endif
        @foreach($columns as $column)
            {{ $columnLabelRenderer->build($request, $column) }}
        @endforeach
        @if($rowActionCount > 0)
            <th>&nbsp;</th>
        @endif
    </tr>
</thead>