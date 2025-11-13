<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Exceptions;

use BrickNPC\EloquentTables\Contracts\Formatter;

class InvalidValueException extends \Exception
{
    public mixed $value = null {
        get => $this->value;
    }

    public ?Formatter $formatter = null {
        get => $this->formatter;
    }

    public static function forInvalidValue(mixed $value, Formatter $formatter): self
    {
        $text = match (true) {
            is_object($value)   => 'of type ' . get_class($value),
            is_array($value)    => 'of type array',
            is_resource($value) => 'of type resource',
            is_callable($value) => 'of type callable',
            default             => $value,
        };

        $exception            = new self(__('The value :value is not a valid value for formatting.', ['value' => $text ?? 'null']));
        $exception->value     = $value;
        $exception->formatter = $formatter;

        return $exception;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'invalid_value' => $this->value,
            'formatter'     => $this->formatter,
        ];
    }
}
