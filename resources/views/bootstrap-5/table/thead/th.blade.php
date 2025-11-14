@php
    use BrickNPC\EloquentTables\Column;
    use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;

    /** @var Column $column */
    /** @var ColumnLabelViewBuilder $builder */
@endphp
<th>
    <div class="d-flex justify-content-between">
        {{ $builder->build($request, $column) }}
    </div>
</th>