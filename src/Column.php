<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Enums\ColumnType;
use BrickNPC\EloquentTables\Enums\TableStyle;
use BrickNPC\EloquentTables\Contracts\Formatter;
use Illuminate\Contracts\Database\Query\Builder;
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
     * @param null|\Closure(TModel $model): \Stringable                                  $valueUsing
     * @param null|\Closure(Request $request, Builder $query, Sort $direction): void     $sortUsing
     * @param null|\Closure(Request $request, Builder $query, string $searchQuery): void $searchUsing
     * @param null|class-string<Formatter>|Formatter                                     $formatter
     * @param TableStyle[]                                                               $styles
     * @param CellStyle[]                                                                $cellStyles
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
        public array $styles = [],
        public array $cellStyles = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getFormatterParameters(): array
    {
        return $this->formatterParameters;
    }

    /**
     * @param \Closure(TModel $model): \Stringable $valueUsing
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
     */
    public function sortable(?\Closure $sortUsing = null, ?Sort $default = null): static
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

    public function date(): static
    {
        return $this->format(DateFormatter::class);
    }

    public function dateTime(): static
    {
        return $this->format(DateTimeFormatter::class);
    }

    public function number(int $decimals = 0, ?string $locale = null): static
    {
        $this->formatterParameters = ['decimals' => $decimals];

        if (null !== $locale) {
            $this->formatterParameters['locale'] = $locale;
        }

        return $this->format(NumberFormatter::class);
    }

    public function float(int $decimals = 2, ?string $locale = null): static
    {
        $this->formatterParameters = ['decimals' => $decimals];

        if (null !== $locale) {
            $this->formatterParameters['locale'] = $locale;
        }

        return $this->format(NumberFormatter::class);
    }

    public function currency(?string $currency = null, ?string $locale = null): static
    {
        $this->formatterParameters = [];

        if (null !== $currency) {
            $this->formatterParameters['currency'] = $currency;
        }

        if (null !== $locale) {
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

    public function styles(TableStyle ...$styles): static
    {
        $this->styles = array_merge($this->styles, $styles);

        return $this;
    }

    public function cellStyles(CellStyle ...$cellStyles): static
    {
        $this->cellStyles = array_merge($this->cellStyles, $cellStyles);

        return $this;
    }
}
