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
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\TableStyle;
use BrickNPC\EloquentTables\Services\LayoutFinder;

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
     * @param RowActionViewBuilder<TModel>   $rowActionBuilder
     * @param ColumnLabelViewBuilder<TModel> $columnLabelViewBuilder
     * @param ColumnValueViewBuilder<TModel> $columnValueViewBuilder
     * @param LayoutFinder<TModel>           $layoutFinder
     * @param RowsBuilder<TModel>            $rowsBuilder
     */
    public function __construct(
        private ColumnLabelViewBuilder $columnLabelViewBuilder,
        private ColumnValueViewBuilder $columnValueViewBuilder,
        private TableActionViewBuilder $tableActionViewBuilder,
        private RowActionViewBuilder $rowActionBuilder,
        private Factory $viewFactory,
        private LayoutFinder $layoutFinder,
        private Config $config,
        private RowsBuilder $rowsBuilder,
        private MassActionViewBuilder $massActionViewBuilder,
        private FilterViewBuilder $filterViewBuilder,
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

        $viewData = [
            'id'            => spl_object_id($table),
            'theme'         => $theme,
            'dataNamespace' => $this->config->dataNamespace(),
            'request'       => $request,
            'tableStyles'   => collect($table->tableStyles())
                ->map(fn (TableStyle $style) => $style->toCssClass($theme))
                ->implode(' '),
            'columns'                 => $table->columns(),
            'columnLabelViewBuilder'  => $this->columnLabelViewBuilder,
            'rows'                    => $this->getRows($table, $request),
            'columnValueViewBuilder'  => $this->columnValueViewBuilder,
            'links'                   => $this->getLinks($table, $request),
            'tableActionCount'        => count($table->tableActions()),
            'tableActions'            => $table->tableActions(),
            'tableActionViewBuilder'  => $this->tableActionViewBuilder,
            'showSearchForm'          => $this->hasSearchableColumns($table->columns()),
            'tableSearchUrl'          => $request->fullUrlWithQuery([$this->config->searchQueryName() => $request->query($this->config->searchQueryName())]),
            'searchQuery'             => $request->query($this->config->searchQueryName()),
            'searchQueryName'         => $this->config->searchQueryName(),
            'searchIcon'              => $this->config->searchIcon(),
            'rowActionCount'          => count($table->rowActions()),
            'rowActions'              => $table->rowActions(),
            'rowActionBuilder'        => $this->rowActionBuilder,
            'massActionCount'         => count($table->massActions()),
            'massActions'             => $table->massActions(),
            'massActionViewBuilder'   => $this->massActionViewBuilder,
            'filterCount'             => count($table->filters()),
            'filters'                 => $table->filters(),
            'filterViewBuilder'       => $this->filterViewBuilder,
        ];

        $layout = $this->layoutFinder->getLayout($table);
        if (null !== $layout) {
            $viewData['layout'] = $layout;
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

        return $this->rowsBuilder->build($table, $request)->links($theme->getLinksView()); // @phpstan-ignore-line
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
