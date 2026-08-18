<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Contracts\Style;
use BrickNPC\EloquentTables\Enums\ColumnType;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use BrickNPC\EloquentTables\Contracts\Formatter;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Formatters\DateFormatter;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;
use BrickNPC\EloquentTables\Formatters\DateTimeFormatter;

/**
 * @template TModel of Model
 */
class Column
{
    /**
     * @var array<string, mixed>
     */
    private array $formatterParameters = [];

    /**
     * @param null|(\Closure(TModel $model): (float|Htmlable|int|string|\Stringable))    $valueUsing
     * @param null|\Closure(Request $request, Builder $query, Sort $direction): void     $sortUsing
     * @param null|\Closure(Request $request, Builder $query): void|Sort                 $defaultSort
     * @param null|\Closure(Request $request, Builder $query, string $searchQuery): void $searchUsing
     * @param null|class-string<Formatter>|Formatter                                     $formatter
     * @param Aggregate[]                                                                $aggregates
     */
    public function __construct(
        public string $name,
        public ?\Closure $valueUsing = null,
        public ?string $label = null,
        public bool $sortable = false,
        public ?\Closure $sortUsing = null,
        public \Closure|Sort|null $defaultSort = null,
        public bool $searchable = false,
        public ?\Closure $searchUsing = null,
        public Formatter|string|null $formatter = null,
        public ?ColumnType $type = ColumnType::Text,
        public ?StyleSet $style = null,
        public array $aggregates = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getFormatterParameters(): array
    {
        return $this->formatterParameters;
    }

    /**
     * @param (\Closure(TModel $model): (float|Htmlable|int|string|\Stringable)) $valueUsing
     */
    public function valueUsing(\Closure $valueUsing): static
    {
        $this->valueUsing = $valueUsing;

        return $this;
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @param null|\Closure(Request $request, Builder $query, Sort $direction): void $sortUsing
     * @param null|\Closure(Request $request, Builder $query): void|Sort             $default
     */
    public function sortable(?\Closure $sortUsing = null, \Closure|Sort|null $default = null): static
    {
        $this->sortable    = true;
        $this->sortUsing   = $sortUsing;
        $this->defaultSort = $default;

        return $this;
    }

    /**
     * @param null|\Closure(Request $request, Builder $query, string $searchQuery): void $searchUsing
     */
    public function searchable(?\Closure $searchUsing = null): static
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

        // @mago-expect analysis:possibly-invalid-argument
        if (is_callable($this->searchUsing)) {
            call_user_func($this->searchUsing, $request, $query, $searchQuery);
        } else {
            $query->where($this->name, 'like', '%' . $searchQuery . '%');
        }
    }

    /**
     * @param class-string<Formatter>|Formatter $formatter
     */
    public function format(Formatter|string $formatter): static
    {
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @param null|(\Closure(TModel $model): string)|string                               $locale
     * @param null|(\Closure(TModel $model): (\DateTimeZone|string))|\DateTimeZone|string $timezone
     */
    public function date(\Closure|string|null $locale = null, \Closure|\DateTimeZone|string|null $timezone = null): static
    {
        $this->formatterParameters = $this->dateParameters($locale, $timezone);

        return $this->format(DateFormatter::class);
    }

    /**
     * @param null|(\Closure(TModel $model): string)|string                               $locale
     * @param null|(\Closure(TModel $model): (\DateTimeZone|string))|\DateTimeZone|string $timezone
     */
    public function dateTime(\Closure|string|null $locale = null, \Closure|\DateTimeZone|string|null $timezone = null): static
    {
        $this->formatterParameters = $this->dateParameters($locale, $timezone);

        return $this->format(DateTimeFormatter::class);
    }

    /**
     * @param (\Closure(TModel $model): int)|int            $decimals
     * @param null|(\Closure(TModel $model): string)|string $locale
     */
    public function number(\Closure|int $decimals = 0, \Closure|string|null $locale = null): static
    {
        $this->formatterParameters = ['decimals' => $decimals];

        if ($locale !== null) {
            $this->formatterParameters['locale'] = $locale;
        }

        return $this->format(NumberFormatter::class);
    }

    /**
     * @param (\Closure(TModel $model): int)|int            $decimals
     * @param null|(\Closure(TModel $model): string)|string $locale
     */
    public function float(\Closure|int $decimals = 2, \Closure|string|null $locale = null): static
    {
        return $this->number($decimals, $locale);
    }

    /**
     * @param null|(\Closure(TModel $model): string)|string $currency
     * @param null|(\Closure(TModel $model): string)|string $locale
     */
    public function currency(\Closure|string|null $currency = null, \Closure|string|null $locale = null): static
    {
        $this->formatterParameters = [];

        if ($currency !== null) {
            $this->formatterParameters['currency'] = $currency;
        }

        if ($locale !== null) {
            $this->formatterParameters['locale'] = $locale;
        }

        return $this->format(CurrencyFormatter::class);
    }

    public function type(ColumnType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function checkbox(): static
    {
        return $this->type(ColumnType::Checkbox);
    }

    public function boolean(): static
    {
        return $this->type(ColumnType::Boolean);
    }

    public function style(\Closure|Style ...$styles): static
    {
        $this->style = $this->style?->with(...$styles) ?? new StyleSet(...$styles);

        return $this;
    }

    public function aggregate(Aggregate ...$aggregates): static
    {
        $this->aggregates = [...$this->aggregates, ...$aggregates];

        return $this;
    }

    /**
     * @param null|(\Closure(TModel $model): string)|string                               $locale
     * @param null|(\Closure(TModel $model): (\DateTimeZone|string))|\DateTimeZone|string $timezone
     *
     * @return array<string, mixed>
     */
    private function dateParameters(\Closure|string|null $locale, \Closure|\DateTimeZone|string|null $timezone): array
    {
        $parameters = [];

        if ($locale !== null) {
            $parameters['locale'] = $locale;
        }

        if ($timezone !== null) {
            $parameters['timezone'] = $timezone;
        }

        return $parameters;
    }
}
