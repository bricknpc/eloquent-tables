@php
    use BrickNPC\EloquentTables\Column;
    use BrickNPC\EloquentTables\ValueObjects\ResolvedFooter;

    /** @var ResolvedFooter $footer */
    /** @var Column[] $columns */
@endphp
@if(!$footer->isEmpty())
    <tfoot>
        @foreach($footer->rows as $footerRow)
            <tr class="{{ $footerRow->styles }}">
                @if($footerRow->labelIndex === null)
                    @if($footer->labelSpan > 0)
                        <th colspan="{{ $footer->labelSpan }}">{{ $footerRow->label }}</th>
                    @endif
                    @foreach($columns as $index => $column)
                        @if($index >= $footer->firstValueIndex)
                            <td>{{ $footerRow->values[$index] ?? '' }}</td>
                        @endif
                    @endforeach
                @else
                    @if($bulkActionCount > 0)
                        <td></td>
                    @endif
                    @foreach($columns as $index => $column)
                        @if($index === $footerRow->labelIndex)
                            <th>{{ $footerRow->label }}</th>
                        @else
                            <td>{{ $footerRow->values[$index] ?? '' }}</td>
                        @endif
                    @endforeach
                @endif
                @if($rowActionCount > 0)
                    <td></td>
                @endif
            </tr>
        @endforeach
    </tfoot>
@endif
