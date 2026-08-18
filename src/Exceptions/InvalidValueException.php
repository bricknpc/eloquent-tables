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
        // @mago-ignore analysis:possibly-invalid-argument,possibly-null-argument -- __() is typed differently across
        // Laravel 12 and 13, so which of these two fires depends on the installed version. @mago-ignore rather than
        // @mago-expect because exactly one of them is unused on any given version.

        $exception = new self(__('The value :value is not a valid value for formatting.', [
            'value' => $text ?? 'null',
        ]));
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
