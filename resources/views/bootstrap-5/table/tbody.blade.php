@php
    use Illuminate\Support\Collection;
    use BrickNPC\EloquentTables\Column;
    use Illuminate\Database\Eloquent\Model;
    use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;

    /** @var Collection<int, Model> $rows */
    /** @var ColumnValueViewBuilder $columnValueViewBuilder */
    /** @var Column[] $columns */
@endphp
<tbody>
    @foreach($rows as $row)
        <tr>
            @foreach($columns as $column)
                @php
                    /** @var Model $row */
                @endphp
                {{ $columnValueViewBuilder->build($request, $column, $row) }}
            @endforeach
        </tr>
    @endforeach
</tbody>