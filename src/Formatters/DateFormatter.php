<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Formatters;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Contracts\Formatter;
use BrickNPC\EloquentTables\Exceptions\InvalidValueException;

readonly class DateFormatter implements Formatter
{
    public function __construct(
        private string $locale,
        private \DateTimeZone $timezone,
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
        $formatter = new \IntlDateFormatter(
            locale: $this->locale,
            dateType: \IntlDateFormatter::FULL,
            timeType: \IntlDateFormatter::NONE,
            timezone: $this->timezone,
        );

        $formatted = $formatter->format($value);  // @phpstan-ignore argument.type

        if ($formatted === false) {
            throw InvalidValueException::forInvalidValue($value, $this);
        }

        return str($formatted);
    }
}
