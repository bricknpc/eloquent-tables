<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Contracts\Filter;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Enums\TableParameter;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Services\RouteModelBinder;
use Illuminate\Pagination\AbstractPaginator as Paginator;

/**
 * @template TModel of Model
 */
class RowsBuilder
{
    /**
     * @var Collection<int, Column<TModel>>
     */
    private Collection $columns;

    /**
     * @var null|Collection<int, Model>|Paginator<int, Model>
     */
    private Collection|Paginator|null $result = null;

    private ?Builder $narrowedQuery = null;

    /**
     * @param TableParameters<TModel> $parameters
     */
    public function __construct(
        private readonly RouteModelBinder $routeModelBinder,
        private readonly TableParameters $parameters,
    ) {}

    /**
     * @param Table|WithPagination $table
     *
     * @return Collection<int, Model>|Paginator<int, Model>
     */
    public function build(Table $table, Request $request, bool $forceReload = false): Collection|Paginator // @phpstan-ignore-line
    {
        if ($this->result !== null && !$forceReload) {
            return $this->result;
        }

        /** @var array<int, Column<TModel>> $columns */
        $columns = $this->routeModelBinder->call($table, 'columns');

        /** @var Collection<int, Column<TModel>> $collected */
        $collected = collect($columns);

        $this->columns = $collected;

        /** @var Builder $query */
        $query = $this->routeModelBinder->call($table, 'query');

        $this->applySearch($query, $table, $request);
        $this->applyFilters($query, $table, $request);
        $this->applySort($query, $table, $request);

        // Retained before pagination, so a footer aggregate can run against the same narrowed
        // set the rows came from without the page limit.
        $this->narrowedQuery = clone $query;

        /** @var Collection<int, Model>|Paginator<int, Model> $result */
        $result = $table->withPagination()
            ? $query->paginate(
                perPage: $this->parameters->perPage($table, $request, $table->perPage($request)), // @phpstan-ignore-line
                pageName: $this->parameters->key($table, TableParameter::Page),
                // Laravel resolves the current page with $request->input($pageName), which reads dot
                // notation and cannot see a bracketed key, so the page is resolved here instead.
                page: $this->parameters->integerValue($table, TableParameter::Page, $request),
            )->withQueryString()
            : $query->get();

        return $this->result = $result;
    }

    /**
     * The query behind the rows, with search, filters and sorting applied but no page limit.
     *
     * Null until build() has run. A fresh clone is returned each call so one aggregate cannot
     * affect the next.
     */
    public function narrowedQuery(): ?Builder
    {
        return $this->narrowedQuery === null ? null : clone $this->narrowedQuery;
    }

    /**
     * @param Table<TModel> $table
     */
    private function applyFilters(Builder $query, Table $table, Request $request): void
    {
        if (!$table->hasFilters()) {
            return;
        }

        $filterRequest = $this->parameters->arrayValue($table, TableParameter::Filter, $request);

        /** @var Filter[] $filters */
        $filters = $this->routeModelBinder->call($table, 'filters');

        collect($filters)
            ->filter(fn (Filter $filter) => array_key_exists($filter->name, $filterRequest))
            ->each(fn (Filter $filter) => call_user_func($filter, $request, $query, $filterRequest[$filter->name]))
        ;
    }

    /**
     * @param Table<TModel> $table
     */
    private function applySort(Builder $query, Table $table, Request $request): void
    {
        $sortRequest = $this->parameters->arrayValue($table, TableParameter::Sort, $request);

        $sortable = $this->columns
            ->filter(fn (Column $column) => $column->sortable)
            ->keyBy(fn (Column $column) => $column->name)
        ;

        $sorted = false;

        // Driven by the request rather than the column list, so the order the visitor clicked the
        // headers is the order the sort is applied in.
        foreach ($sortRequest as $name => $direction) {
            /** @var null|Column<TModel> $column */
            $column = $sortable->get($name);
            $sort   = Sort::tryFrom($direction);

            if ($column === null || $sort === null) {
                continue;
            }

            $sorted = true;

            if ($column->sortUsing !== null) {
                call_user_func($column->sortUsing, $request, $query, $sort);
            } else {
                $query->orderBy($column->name, $sort->value);
            }
        }

        if ($sorted) {
            return;
        }

        // Nothing usable came from the visitor, so fall back to whatever the columns declare.
        $this->columns
            ->filter(fn (Column $column) => $column->sortable && $column->defaultSort !== null)
            ->each(function (Column $column) use ($query, $request) {
                if ($column->defaultSort instanceof \Closure) {
                    call_user_func($column->defaultSort, $request, $query);
                } else {
                    $query->orderBy($column->name, $column->defaultSort?->value); // @phpstan-ignore-line
                }
            })
        ;
    }

    /**
     * @param Table<TModel> $table
     */
    private function applySearch(Builder $query, Table $table, Request $request): void
    {
        $search = $this->parameters->stringValue($table, TableParameter::Search, $request);

        if ($search === null) {
            return;
        }

        $query->where(function (Builder $query) use ($search, $request) {
            $this->columns
                ->filter(fn (Column $column) => $column->searchable)
                ->each(function (Column $column) use ($search, $request, $query) {
                    $query->orWhere(fn (Builder $query) => $column->search($request, $query, $search));
                })
            ;
        });
    }
}
