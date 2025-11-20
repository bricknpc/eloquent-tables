<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Contracts\Filter;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Concerns\WithPagination;
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

    public function __construct(
        private readonly Config $config,
        private readonly RouteModelBinder $methodInvoker,
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
        $columns = $this->methodInvoker->call($table, 'columns');

        /** @var Collection<int, Column<TModel>> $collected */
        $collected = collect($columns);

        $this->columns = $collected;

        /** @var Builder $query */
        $query = $this->methodInvoker->call($table, 'query');

        $this->applySearch($query, $request);
        $this->applyFilters($query, $table, $request);
        $this->applySort($query, $request);

        /** @var Collection<int, Model>|Paginator<int, Model> $result */
        $result = $table->withPagination()
            ? $query->paginate($table->getPerPage($request), $table->perPageName) // @phpstan-ignore-line
            : $query->get();

        return $this->result = $result;
    }

    /**
     * @param Table<TModel> $table
     */
    private function applyFilters(Builder $query, Table $table, Request $request): void
    {
        /** @var array<string, string>|string $filterRequest */
        $filterRequest = $request->query('filter', []);

        if (!is_array($filterRequest)) {
            $filterRequest = [];
        }

        foreach ($filterRequest as $key => $value) {
            if (empty($value)) {
                unset($filterRequest[$key]);
            }
        }

        collect($table->filters())
            ->filter(fn (Filter $filter) => array_key_exists($filter->name, $filterRequest))
            ->each(fn (Filter $filter) => $filter(
                $request,
                $query,
                $filterRequest[$filter->name],
            ))
        ;
    }

    private function applySort(Builder $query, Request $request): void
    {
        /** @var array<string, string>|string $sortRequest */
        $sortRequest = $request->query($this->config->sortQueryName(), []);

        if (!is_array($sortRequest)) {
            $sortRequest = [];
        }

        $this->columns
            ->filter(fn (Column $column) => $column->sortable)
            ->filter(fn (Column $column) => array_key_exists($column->name, $sortRequest) || $column->defaultSort !== null)
            ->each(function (Column $column) use ($sortRequest, $query) {
                if (array_key_exists($column->name, $sortRequest)) {
                    $sort = Sort::from($sortRequest[$column->name]);

                    $query->orderBy($column->name, $sort->value);

                    return;
                }

                $query->orderBy($column->name, $column->defaultSort->value); // @phpstan-ignore-line
            })
        ;
    }

    private function applySearch(Builder $query, Request $request): void
    {
        $search = $request->query($this->config->searchQueryName());

        if (!is_string($search) || empty($search)) {
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
