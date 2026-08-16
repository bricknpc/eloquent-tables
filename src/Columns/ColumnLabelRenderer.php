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
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use BrickNPC\EloquentTables\Enums\TableRegion;
use BrickNPC\EloquentTables\Enums\TableParameter;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Styles\Contexts\CellContext;

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
        private StyleResolver $styleResolver,
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

        $styles = $column->style?->resolve(
            new CellContext($request, $column, null, TableRegion::Header),
        ) ?? [];

        $styles = $this->styleResolver->withDefaults($styles, $column->type?->defaultStyles() ?? []);

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
            'styles'         => $this->styleResolver->classes($styles, StyleTarget::Cell),
            'cellStyles'     => $this->styleResolver->classes($styles, StyleTarget::Content),
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

        $namespace[$this->config->sortQueryName()] = $sort === [] ? '' : $sort;

        return $request->fullUrlWithQuery([$table->name() => $namespace]);
    }
}
