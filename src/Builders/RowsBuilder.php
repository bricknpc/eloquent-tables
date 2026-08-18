<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\AbstractPaginator as Paginator;
use BrickNPC\EloquentTables\Contracts\Filter;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Enums\TableParameter;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Services\RouteModelBinder;

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

    // @mago-expect analysis:docblock-type-mismatch -- WithPagination is a trait, so it cannot be half of a union type
    /**
     * @param Table|WithPagination $table
     *
     * @return Collection<int, Model>|Paginator<int, Model>
     */
    public function build(Table $table, Request $request, bool $forceReload = false): Collection|Paginator
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

        // @mago-expect analysis:possibly-invalid-argument -- the query comes back from a container call, so its type is unprovable here
        $this->applySearch($query, $table, $request);
        // @mago-expect analysis:possibly-invalid-argument -- the query comes back from a container call, so its type is unprovable here
        $this->applyFilters($query, $table, $request);
        // @mago-expect analysis:possibly-invalid-argument -- the query comes back from a container call, so its type is unprovable here
        $this->applySort($query, $table, $request);

        // Retained before pagination, so a footer aggregate can run against the same narrowed
        // set the rows came from without the page limit.
        $this->narrowedQuery = clone $query;

        /** @var Collection<int, Model>|Paginator<int, Model> $result */
        $result = $table->withPagination()
            // @mago-expect analysis:mixed-argument,non-existent-method,possibly-invalid-argument -- WithPagination is optional, so a trait method is unprovable on Table
            ? $query
                ->paginate(
                    // @mago-expect analysis:possibly-invalid-argument -- WithPagination is optional, so a trait method is unprovable on Table
                    perPage: $this->parameters->perPage($table, $request, $table->perPage($request)),
                    pageName: $this->parameters->key($table, TableParameter::Page),
                    // Laravel resolves the current page with $request->input($pageName), which reads dot
                    // notation and cannot see a bracketed key, so the page is resolved here instead.
                    // @mago-expect analysis:possibly-invalid-argument -- the query comes back from a container call, so its type is unprovable here
                    page: $this->parameters->integerValue($table, TableParameter::Page, $request),
                )
                ->withQueryString()
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
            // @mago-expect analysis:possibly-invalid-argument -- the filter callback is user-supplied, so its signature is unprovable here
            ->filter(static fn(Filter $filter) => array_key_exists($filter->name, $filterRequest))
            ->each(static fn(Filter $filter) => call_user_func(
                $filter,
                $request,
                $query,
                $filterRequest[$filter->name],
            ));
    }

    /**
     * @param Table<TModel> $table
     */
    private function applySort(Builder $query, Table $table, Request $request): void
    {
        $sortRequest = $this->parameters->arrayValue($table, TableParameter::Sort, $request);

        $sortable = $this->columns
            ->filter(static fn(Column $column) => $column->sortable)
            ->keyBy(static fn(Column $column) => $column->name);

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

            // @mago-expect analysis:possibly-invalid-argument -- sortUsing is user-supplied, so its signature is unprovable here
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
            ->filter(static fn(Column $column) => $column->sortable && $column->defaultSort !== null)
            ->each(static function (Column $column) use ($query, $request) {
                if ($column->defaultSort instanceof \Closure) {
                    call_user_func($column->defaultSort, $request, $query);

                    // @mago-expect analysis:possibly-null-argument -- defaultSort is non-null on this branch, which the analyzer does not carry from the filter above
                } else {
                    $query->orderBy($column->name, $column->defaultSort?->value);
                }
            });
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
                ->filter(static fn(Column $column) => $column->searchable)
                ->each(static function (Column $column) use ($search, $request, $query) {
                    $query->orWhere(static fn(Builder $query) => $column->search($request, $query, $search));
                });
        });
    }
}
