<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Formatters;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Contracts\Formatter;

readonly class CurrencyFormatter implements Formatter
{
    public function __construct(
        private string $locale,
        private string $currency,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param null|TModel $model
     */
    public function format(mixed $value, ?Model $model = null): \Stringable
    {
        $formatter = new \NumberFormatter($this->locale, \NumberFormatter::CURRENCY);
        // @mago-expect analysis:mixed-argument -- the Formatter contract takes mixed; the guard above narrows it at runtime

        return str((string) $formatter->formatCurrency($value, $this->currency));
    }
}
