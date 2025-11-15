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
        @foreach($columns as $column)
            {{ $columnLabelViewBuilder->build($request, $column) }}
        @endforeach
        @if(count($rowActions) > 0)
            <th>&nbsp;</th>
        @endif
    </tr>
</thead>