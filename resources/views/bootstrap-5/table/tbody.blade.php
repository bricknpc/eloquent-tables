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
            @if($massActionCount > 0)
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $row->getKey() }}" />
                </td>
            @endif
            @foreach($columns as $column)
                @php
                    /** @var Model $row */
                @endphp
                {{ $columnValueViewBuilder->build($request, $column, $row) }}
            @endforeach
            @if($rowActionCount > 0)
                <td class="text-end">
                    <div class="btn-group">
                        @foreach($rowActions as $action)
                            {{ $rowActionBuilder->build($action, $request, $row) }}
                        @endforeach
                    </div>
                </td>
            @endif
        </tr>
    @endforeach
</tbody>