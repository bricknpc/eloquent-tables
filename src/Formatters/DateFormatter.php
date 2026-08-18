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
        private \DateTimeZone|string $timezone,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param null|TModel $model
     *
     * @throws InvalidValueException
     */
    public function format(mixed $value, ?Model $model = null): \Stringable
    {
        $formatter = new \IntlDateFormatter(
            locale: $this->locale,
            dateType: \IntlDateFormatter::FULL,
            timeType: \IntlDateFormatter::NONE,
            timezone: $this->timezone(),
        );
        // @mago-expect analysis:mixed-argument

        $formatted = $formatter->format($value);  // @phpstan-ignore argument.type

        if ($formatted === false) {
            throw InvalidValueException::forInvalidValue($value, $this);
        }

        return str($formatted);
    }

    /**
     * @throws InvalidValueException
     */
    private function timezone(): \DateTimeZone
    {
        if ($this->timezone instanceof \DateTimeZone) {
            return $this->timezone;
        }

        try {
            return new \DateTimeZone($this->timezone);
        } catch (\Exception) {
            throw InvalidValueException::forInvalidValue($this->timezone, $this);
        }
    }
}
