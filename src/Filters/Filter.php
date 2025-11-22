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
    /**
     * @var null|\Closure(Request, Builder, string): void
     */
    protected ?\Closure $filter = null;

    public function __construct(
        public readonly string $name,
        public readonly Collection|iterable $options,
        public ?string $optionKey = null,
        public ?string $optionLabel = null,
    ) {}

    public function __invoke(Request $request, Builder $query, string $value): void
    {
        $this->filter !== null
            ? call_user_func($this->filter, $request, $query, $value)
            : $query->where($this->name, '=', $value);
    }

    /**
     * @param \Closure(Request $request, Builder $query, string $value): void $filter
     */
    public function filter(\Closure $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function optionKey(string $optionKey): self
    {
        $this->optionKey = $optionKey;

        return $this;
    }

    public function optionLabel(string $optionLabel): self
    {
        $this->optionLabel = $optionLabel;

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
        $result = $options
            ->mapWithKeys(fn (mixed $option, int|string $key) => $this->getOption($option, $key)) // @phpstan-ignore-line
            ->toArray()
        ;

        return $result;
    }

    /**
     * @param \BackedEnum|bool|float|int|Model|string|\Stringable|\UnitEnum $option
     *
     * @return array<string, string>
     */
    private function getOption(mixed $option, int|string $key): array
    {
        if ($option instanceof Model) {
            $key   = $this->optionKey     ?? $option->getKeyName();
            $label = $this->optionLabel   ?? $option->getKeyName();

            return [(string) $option->{$key} => (string) $option->{$label}]; // @phpstan-ignore-line
        }

        if ($option instanceof \BackedEnum) {
            return [(string) $option->value => $option->name];
        }

        if ($option instanceof \UnitEnum) {
            return [$option->name => $option->name];
        }

        return [(string) $key => (string) $option];
    }
}
