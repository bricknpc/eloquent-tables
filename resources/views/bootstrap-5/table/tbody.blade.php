@php
    use Illuminate\Support\Collection;
    use BrickNPC\EloquentTables\Column;
    use Illuminate\Database\Eloquent\Model;
    use BrickNPC\EloquentTables\Actions\ActionRenderer;
    use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
    use BrickNPC\EloquentTables\Columns\ColumnValueRenderer;

    /** @var Collection<int, Model> $rows */
    /** @var ColumnValueRenderer $columnValueRenderer */
    /** @var Column[] $columns */
    /** @var ActionRenderer $actionRenderer */
@endphp
<tbody>
    @foreach($rows as $row)
        <tr>
            @if($bulkActionCount > 0)
                <td class="text-center">
                    <input type="checkbox" name="selected[]" value="{{ $row->getKey() }}" />
                </td>
            @endif
            @foreach($columns as $column)
                @php
                    /** @var Model $row */
                @endphp
                {{ $columnValueRenderer->build($request, $column, $row) }}
            @endforeach
            @if($rowActionCount > 0)
                <td class="text-end">
                    <div class="btn-group">
                        @foreach($rowActions as $action)
                            {!! $actionRenderer->render($action, new ActionContext($request, $config, $row)) !!}
                        @endforeach
                    </div>
                </td>
            @endif
        </tr>
    @endforeach
</tbody>