<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Formatters;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Contracts\Formatter;
use BrickNPC\EloquentTables\Exceptions\InvalidValueException;

readonly class NumberFormatter implements Formatter
{
    public function __construct(
        private string $locale,
        private int $decimals = 2,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param TModel $model
     *
     * @throws InvalidValueException
     */
    public function format(mixed $value, Model $model): \Stringable
    {
        if (!is_numeric($value)) {
            throw InvalidValueException::forInvalidValue($value, $this);
        }

        $formatter = new \NumberFormatter($this->locale, \NumberFormatter::DECIMAL);

        $decimalSeparator   = $formatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        $thousandsSeparator = $formatter->getSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL);

        return str(number_format((float) $value, $this->decimals, $decimalSeparator, $thousandsSeparator));
    }
}
