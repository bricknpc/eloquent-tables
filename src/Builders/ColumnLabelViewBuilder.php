<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Enums\Sort;

readonly class ColumnLabelViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
    ) {}

    public function build(Request $request, Column $column): View
    {
        return $this->viewFactory->make('eloquent-tables::column-label', [
            'label'         => $this->getLabelValue($column),
            'sortable'      => $column->sortable,
            'searchable'    => $column->searchable,
            'isSorted'      => false, // todo
            'sortDirection' => null, // todo
            'href'          => $request->fullUrlWithQuery([
                'sort[' . $column->name . ']' => Sort::Asc, // todo
            ]),
        ]);
    }

    private function getLabelValue(Column $column): string
    {
        return $column->label ?? str($column->name)->title()->value();
    }
}
