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
        <tr class="{{ $rowStyles[$loop->index] ?? '' }}">
            @if($bulkActionCount > 0)
                <td>
                    <div class="d-flex align-items-center w-100 {{ $bulkActionCellStyles }}">
                        <input type="checkbox" name="selected[]" value="{{ $row->getKey() }}" />
                    </div>
                </td>
            @endif
            @foreach($columns as $column)
                @php
                    /** @var Model $row */
                @endphp
                {{ $columnValueRenderer->build($request, $column, $row) }}
            @endforeach
            @if($rowActionCount > 0)
                @php
                    $rowContext = new ActionContext($request, $config, $row);
                @endphp
                <td>
                    <div class="d-flex align-items-center w-100 {{ $rowActionCellStyles }}">
                        @if($actionRenderer->countRenderable($rowActions, $rowContext) > 0)
                            <div class="btn-group">
                                @foreach($rowActions as $action)
                                    {!! $actionRenderer->render($action, $rowContext) !!}
                                @endforeach
                            </div>
                        @endif
                    </div>
                </td>
            @endif
        </tr>
    @endforeach
</tbody>