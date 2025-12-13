<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Contracts\Filter;
use BrickNPC\EloquentTables\Enums\TableStyle;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Services\RouteModelBinder;

/**
 * @template TModel of Model
 *
 * <todo>
 * All the ignore comments for PHPStan in this class are because of the fact that the WithPagination trait is
 * optional for tables. PHPStan does not have a valid way to handle this, so instead of writing a bunch of
 * unnecessary code I choose to ignore PHPStan in this regard, even though it is not best practice.
 *
 * If a better solution exists or is implemented in the future, feel free to open a PR.
 * </todo>
 */
readonly class TableViewBuilder
{
    /**
     * @param ColumnLabelViewBuilder<TModel> $columnLabelViewBuilder
     * @param ColumnValueViewBuilder<TModel> $columnValueViewBuilder
     * @param LayoutFinder<TModel>           $layoutFinder
     * @param RowsBuilder<TModel>            $rowsBuilder
     */
    public function __construct(
        private ColumnLabelViewBuilder $columnLabelViewBuilder,
        private ColumnValueViewBuilder $columnValueViewBuilder,
        private Factory $viewFactory,
        private LayoutFinder $layoutFinder,
        private Config $config,
        private RowsBuilder $rowsBuilder,
        private FilterViewBuilder $filterViewBuilder,
        private RouteModelBinder $methodInvoker,
        private ActionRenderer $actionRenderer,
    ) {}

    /**
     * @param Table<TModel> $table
     */
    public function build(Table $table, Request $request): View
    {
        return $this->viewFactory->make(
            $this->getViewFile($table),
            $this->getViewData($table, $request),
        );
    }

    /**
     * @param Table<TModel> $table
     */
    private function getViewFile(Table $table): string
    {
        $layout = $this->layoutFinder->getLayout($table);

        return match ($layout) {
            null    => 'eloquent-tables::table',
            default => 'eloquent-tables::table-with-layout',
        };
    }

    /**
     * @param Table<TModel> $table
     *
     * @return array<string, mixed>
     */
    private function getViewData(Table $table, Request $request): array
    {
        $theme = $this->config->theme();

        /** @var Column<TModel>[] $columns */
        $columns = $this->methodInvoker->call($table, 'columns');

        /** @var Filter[] $filters */
        $filters = $table->hasFilters() ? $this->methodInvoker->call($table, 'filters') : [];

        /** @var Action<TModel>[] $tableActions */
        $tableActions = method_exists($table, 'tableActions') ? $this->methodInvoker->call($table, 'tableActions') : [];

        /** @var Action<TModel>[] $rowActions */
        $rowActions = method_exists($table, 'rowActions') ? $this->methodInvoker->call($table, 'rowActions') : [];

        /** @var Action<TModel>[] $massActions */
        $massActions = method_exists($table, 'massActions') ? $this->methodInvoker->call($table, 'massActions') : [];

        $viewData = [
            'id'            => spl_object_id($table),
            'theme'         => $theme,
            'dataNamespace' => $this->config->dataNamespace(),
            'request'       => $request,
            'tableStyles'   => collect($table->tableStyles())
                ->map(fn (TableStyle $style) => $style->toCssClass($theme))
                ->implode(' '),
            'columns'                => $columns,
            'columnLabelViewBuilder' => $this->columnLabelViewBuilder,
            'rows'                   => $this->getRows($table, $request),
            'columnValueViewBuilder' => $this->columnValueViewBuilder,
            'links'                  => $this->getLinks($table, $request),
            'tableActionCount'       => count($tableActions),
            'tableActions'           => $tableActions,
            'showSearchForm'         => $this->hasSearchableColumns($columns),
            'tableSearchUrl'         => $request->fullUrlWithQuery([$this->config->searchQueryName() => $request->query($this->config->searchQueryName())]),
            'fullUrl'                => $request->fullUrl(),
            'searchQuery'            => $request->query($this->config->searchQueryName()),
            'searchQueryName'        => $this->config->searchQueryName(),
            'searchIcon'             => $this->config->searchIcon(),
            'rowActionCount'         => count($rowActions),
            'rowActions'             => $rowActions,
            'massActionCount'        => count($massActions),
            'massActions'            => $massActions,
            'filterCount'            => count($filters),
            'filters'                => $filters,
            'filterViewBuilder'      => $this->filterViewBuilder,
            'actionRenderer'         => $this->actionRenderer,
        ];

        $layout = $this->layoutFinder->getLayout($table);
        if ($layout !== null) {
            $viewData['layout'] = $layout;
        }

        $viewData['mainTableStyle'] = $table->pageStyle()->toCssClass($theme);
        $viewData['disabledStyle']  = $table->pageStyle()->toCssDisabledClass($theme);
        $viewData['activeStyle']    = $table->pageStyle()->toCssActiveClass($theme);

        if ($table->withPagination()) {
            /* @var WithPagination|Table $table */
            $viewData['perPage']        = $table->perPage($request); // @phpstan-ignore-line
            $viewData['perPageName']    = $table->perPageName(); // @phpstan-ignore-line
            $viewData['perPageOptions'] = $table->perPageOptions(); // @phpstan-ignore-line
        }

        return $viewData;
    }

    /**
     * @param Table<TModel> $table
     *
     * @return Collection<int, Model>
     */
    private function getRows(Table $table, Request $request): Collection
    {
        $results = $this->rowsBuilder->build($table, $request);

        return $results instanceof Collection ? $results : $results->getCollection();
    }

    /**
     * @param Table<TModel> $table
     */
    private function getLinks(Table $table, Request $request): ?Htmlable
    {
        if (!$table->withPagination()) {
            return null;
        }

        $theme = $this->config->theme();

        return $this->rowsBuilder->build($table, $request)->links($theme->getLinksView(), [ // @phpstan-ignore-line
            'mainTableStyle' => $table->pageStyle()->toCssClass($theme),
            'disabledStyle'  => $table->pageStyle()->toCssDisabledClass($theme),
            'activeStyle'    => $table->pageStyle()->toCssActiveClass($theme),
        ]);
    }

    /**
     * @param Column<TModel>[] $columns
     */
    private function hasSearchableColumns(array $columns): bool
    {
        return collect($columns)
            ->filter(fn (Column $column) => $column->searchable)
            ->isNotEmpty()
        ;
    }
}
