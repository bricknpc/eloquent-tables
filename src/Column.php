<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Builder;

class Column
{
    /**
     * @param null|\Closure(Model $model): \Stringable                                   $valueUsing
     * @param null|\Closure(Request $request, Builder $query, Sort $direction): void     $sortUsing
     * @param null|\Closure(Request $request, Builder $query, string $searchQuery): void $searchUsing
     */
    public function __construct(
        public string $name,
        public ?\Closure $valueUsing = null,
        public ?string $label = null,
        public bool $sortable = false,
        public ?\Closure $sortUsing = null,
        public ?Sort $defaultSort = null,
        public bool $searchable = false,
        public ?\Closure $searchUsing = null,
    ) {}

    /**
     * @param \Closure(Model $model): \Stringable $valueUsing
     */
    public function valueUsing(\Closure $valueUsing): self
    {
        $this->valueUsing = $valueUsing;

        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param null|\Closure(Request $request, Builder $query, Sort $direction): void $sortUsing
     */
    public function sortable(?\Closure $sortUsing = null, ?Sort $default = null): self
    {
        $this->sortable    = true;
        $this->sortUsing   = $sortUsing;
        $this->defaultSort = $default;

        return $this;
    }

    /**
     * @param null|\Closure(Request $request, Builder $query, string $searchQuery): void $searchUsing
     */
    public function searchable(?\Closure $searchUsing = null): self
    {
        $this->searchable  = true;
        $this->searchUsing = $searchUsing;

        return $this;
    }

    public function renderLabel(Request $request, Factory $viewFactory): View
    {
        return $viewFactory->make('eloquent-tables::column-label', [
            'label'         => $this->label ?? str($this->name)->title()->value(),
            'sortable'      => $this->sortable,
            'searchable'    => $this->searchable,
            'isSorted'      => false, // todo
            'sortDirection' => null, // todo
            'href'          => $request->fullUrlWithQuery([
                'sort[' . $this->name . ']' => Sort::Asc, // todo
            ]),
        ]);
    }

    public function renderValue(Request $request, Factory $viewFactory, Model $model): View
    {
        return $viewFactory->make('eloquent-tables::column-value', [
            'value' => is_callable($this->valueUsing) ? call_user_func($this->valueUsing, $model) : $model->{$this->name},
        ]);
    }
}
