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
        $sortDirection     = $this->sortDirectionForColumn($request, $column);
        $nextSortDirection = $this->getNextSortDirection($sortDirection);

        return $this->viewFactory->make('eloquent-tables::column-label', [
            'label'         => $this->getLabelValue($column),
            'sortable'      => $column->sortable,
            'searchable'    => $column->searchable,
            'isSorted'      => null !== $sortDirection,
            'sortDirection' => $sortDirection,
            'href'          => $request->fullUrlWithQuery(['sort' => $this->getSortArray($request, $column->name, $nextSortDirection)]),
        ]);
    }

    private function getLabelValue(Column $column): string
    {
        return $column->label ?? str($column->name)->title()->value();
    }

    private function sortDirectionForColumn(Request $request, Column $column): ?Sort
    {
        /** @var array<string, string>|string $sort */
        $sort = $request->query('sort', []);

        if (is_array($sort) && array_key_exists($column->name, $sort)) {
            return Sort::from($sort[$column->name]);
        }

        return null;
    }

    private function getNextSortDirection(?Sort $currentSortDirection): Sort
    {
        return Sort::Asc === $currentSortDirection ? Sort::Desc : Sort::Asc;
    }

    /**
     * @return array<string, string>
     */
    private function getSortArray(Request $request, string $name, Sort $direction): array
    {
        /** @var array<string, string> $currentSort */
        $currentSort = is_array($request->query('sort', [])) ? $request->query('sort', []) : [];

        return array_merge($currentSort, [$name => $direction->value]);
    }
}
