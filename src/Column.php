<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\ColumnType;
use BrickNPC\EloquentTables\Contracts\Formatter;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Formatters\DateFormatter;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;
use BrickNPC\EloquentTables\Formatters\DateTimeFormatter;

class Column
{
    /**
     * @param null|\Closure(Model $model): \Stringable                                   $valueUsing
     * @param null|\Closure(Request $request, Builder $query, Sort $direction): void     $sortUsing
     * @param null|\Closure(Request $request, Builder $query, string $searchQuery): void $searchUsing
     * @param null|Formatter|string<class-string<Formatter>>                             $formatter
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
        public Formatter|string|null $formatter = null,
        public ?ColumnType $type = ColumnType::Text,
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

    public function search(Request $request, Builder $query, string $searchQuery): void
    {
        if (!$this->searchable) {
            return;
        }

        if (is_callable($this->searchUsing)) {
            call_user_func($this->searchUsing, $request, $query, $searchQuery);
        } else {
            $query->where($this->name, 'like', '%' . $searchQuery . '%');
        }
    }

    /**
     * @param Formatter|string<class-string<Formatter>> $formatter
     */
    public function format(Formatter|string $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function date(): self
    {
        return $this->format(DateFormatter::class);
    }

    public function dateTime(): self
    {
        return $this->format(DateTimeFormatter::class);
    }

    public function number(): self
    {
        return $this->format(NumberFormatter::class);
    }

    public function currency(): self
    {
        return $this->format(CurrencyFormatter::class);
    }

    public function type(ColumnType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function checkbox(): self
    {
        return $this->type(ColumnType::Checkbox);
    }

    public function boolean(): self
    {
        return $this->type(ColumnType::Boolean);
    }
}
