@php
    use BrickNPC\EloquentTables\Column;
    use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;

    /** @var Column $column */
    /** @var ColumnLabelViewBuilder $builder */
@endphp
<th>
    {{ $builder->build($request, $column) }}
</th>