<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Services\Config;

/**
 * @template TModel of Model
 */
readonly class ColumnLabelViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private Config $config,
    ) {}

    /**
     * @param Column<TModel> $column
     */
    public function build(Request $request, Column $column): View
    {
        $sortDirection     = $this->sortDirectionForColumn($request, $column);
        $nextSortDirection = $this->getNextSortDirection($sortDirection);

        return $this->viewFactory->make('eloquent-tables::table.th', [
            'theme'         => $this->config->theme(),
            'label'         => $this->getLabelValue($column),
            'sortable'      => $column->sortable,
            'searchable'    => $column->searchable,
            'isSorted'      => null !== $sortDirection,
            'sortDirection' => $sortDirection,
            'href'          => $request->fullUrlWithQuery([$this->config->sortQueryName() => $this->getSortArray($request, $column->name, $nextSortDirection)]),
            'iconNone'      => $this->config->sortNoneIcon(),
            'iconAsc'       => $this->config->sortAscIcon(),
            'iconDesc'      => $this->config->sortDescIcon(),
        ]);
    }

    /**
     * @param Column<TModel> $column
     */
    private function getLabelValue(Column $column): string
    {
        return $column->label ?? str($column->name)->title()->value();
    }

    /**
     * @param Column<TModel> $column
     */
    private function sortDirectionForColumn(Request $request, Column $column): ?Sort
    {
        /** @var array<string, string>|string $sort */
        $sort = $request->query($this->config->sortQueryName(), []);

        if (is_array($sort) && array_key_exists($column->name, $sort)) {
            return Sort::from($sort[$column->name]);
        }

        return null;
    }

    private function getNextSortDirection(?Sort $currentSortDirection): ?Sort
    {
        return match ($currentSortDirection) {
            Sort::Asc  => Sort::Desc,
            Sort::Desc => null,
            default    => Sort::Asc,
        };
    }

    /**
     * @return array<string, string>
     */
    private function getSortArray(Request $request, string $name, ?Sort $direction): array
    {
        /** @var array<string, string> $currentSort */
        $currentSort = is_array($request->query($this->config->sortQueryName(), [])) ? $request->query($this->config->sortQueryName(), []) : [];

        unset($currentSort[$name]);

        if (null === $direction) {
            return $currentSort;
        }

        return array_merge($currentSort, [$name => $direction->value]);
    }
}
