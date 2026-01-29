@php
    use Illuminate\Http\Request;
    use BrickNPC\EloquentTables\Column;
    use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;
    use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;

    /** @var Request $request */
    /** @var Column[] $columns */
    /** @var ColumnLabelViewBuilder $columnLabelViewBuilder */
    /** @var ColumnValueViewBuilder $columnValueViewBuilder */
@endphp
<thead>
    <tr>
        @if($bulkActionCount > 0)
            <th class="text-center" style="width: 5%;">
                <div class="form-check form-switch d-flex justify-content-center">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        role="switch"
                        id="mass-action-switch-{{ $id }}"
                        data-{{ $dataNamespace }}-select-all="true"
                        aria-label="{{ __('Select all') }}"
                    >
                </div>
            </th>
        @endif
        @foreach($columns as $column)
            {{ $columnLabelViewBuilder->build($request, $column) }}
        @endforeach
        @if($rowActionCount > 0)
            <th>&nbsp;</th>
        @endif
    </tr>
</thead>