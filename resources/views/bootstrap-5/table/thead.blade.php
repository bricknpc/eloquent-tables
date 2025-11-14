@php
    use BrickNPC\EloquentTables\Column;
    use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;
    use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;

    /** @var Column[] $columns */
    /** @var ColumnLabelViewBuilder $columnLabelViewBuilder */
    /** @var ColumnValueViewBuilder $columnValueViewBuilder */
@endphp
<thead>
    <tr>
        @foreach($columns as $column)
            @include('eloquent-tables::bootstrap-5.table.thead.th', [
                'column'  => $column,
                'builder' => $columnLabelViewBuilder,
            ])
        @endforeach
    </tr>
</thead>