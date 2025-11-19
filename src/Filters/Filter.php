<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Contracts\Filter as FilterContract;

class Filter implements FilterContract
{
    protected ?\Closure $filter = null;

    public function __construct(
        public readonly string $name,
        public readonly Collection|iterable $options,
    ) {}

    public function __invoke(Request $request, Builder $query, mixed $value): void
    {
        null !== $this->filter
            ? call_user_func($this->filter, $request, $query, $value)
            : $query->where($this->name, '=', $value);
    }

    public function filter(\Closure $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function view(): string
    {
        return 'eloquent-tables::filter.filter';
    }

    public function options(): array
    {
        $options = $this->options instanceof Collection ? $this->options : collect($this->options); // @phpstan-ignore-line

        /** @var array<string, string> $result */
        $result = $options->mapWithKeys(function ($option, int $key) {
            return $option instanceof Model
                ? [(string) $option->getKey() => (string) $option->{$this->name}] // @phpstan-ignore-line
                : [(string) $key => (string) $option]; // @phpstan-ignore-line
        })->toArray();

        return $result;
    }
}
