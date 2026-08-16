<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Columns;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\TableParameter;
use BrickNPC\EloquentTables\Services\TableParameters;

/**
 * @template TModel of Model
 */
readonly class ColumnLabelRenderer
{
    /**
     * @param TableParameters<TModel> $parameters
     */
    public function __construct(
        private Factory $viewFactory,
        private Config $config,
        private TableParameters $parameters,
    ) {}

    /**
     * @param Table<TModel>  $table
     * @param Column<TModel> $column
     */
    public function build(Request $request, Table $table, Column $column): View
    {
        $theme             = $this->config->theme();
        $sortDirection     = $this->sortDirectionForColumn($request, $table, $column);
        $nextSortDirection = $this->getNextSortDirection($sortDirection);

        return $this->viewFactory->make('eloquent-tables::table.th', [
            'theme'          => $theme,
            'label'          => $this->getLabelValue($column),
            'sortable'       => $column->sortable,
            'searchable'     => $column->searchable,
            'isSorted'       => $sortDirection !== null,
            'sortDirection'  => $sortDirection,
            'href'           => $this->sortUrl($request, $table, $column->name, $nextSortDirection),
            'iconNone'       => $this->config->sortNoneIcon(),
            'iconAsc'        => $this->config->sortAscIcon(),
            'iconDesc'       => $this->config->sortDescIcon(),
            'type'           => $column->type,
            'cellStylesFlex' => collect($column->cellStyles)->map(fn (CellStyle $style) => $style->toCssClass($theme, true))->implode(' '),
            'cellStyles'     => collect($column->cellStyles)->map(fn (CellStyle $style) => $style->toCssClass($theme, false))->implode(' '),
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
     * @param Table<TModel>  $table
     * @param Column<TModel> $column
     */
    private function sortDirectionForColumn(Request $request, Table $table, Column $column): ?Sort
    {
        $sort = $this->parameters->arrayValue($table, TableParameter::Sort, $request);

        return array_key_exists($column->name, $sort) ? Sort::from($sort[$column->name]) : null;
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
     * Rebuild the table's namespace with the new sort, leaving its other parameters — and every other
     * table's — untouched.
     *
     * @param Table<TModel> $table
     */
    private function sortUrl(Request $request, Table $table, string $name, ?Sort $direction): string
    {
        $namespace = $request->query($table->name());
        $namespace = is_array($namespace) ? $namespace : [];

        $sort = $this->parameters->arrayValue($table, TableParameter::Sort, $request);

        // Re-appending puts the column last, so the sort array records the order of the clicks.
        unset($sort[$name]);

        if ($direction !== null) {
            $sort[$name] = $direction->value;
        }

        // An empty array vanishes from the query string, which would read as "no sort chosen" and let
        // a stored sort reappear. An empty string survives and says the sort was cleared.
        $namespace[$this->config->sortQueryName()] = $sort === [] ? '' : $sort;

        return $request->fullUrlWithQuery([$table->name() => $namespace]);
    }
}
