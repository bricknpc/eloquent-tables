<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Exceptions;

class MissingMethodException extends \Exception
{
    private ?string $method = null;

    public static function forMethod(string $method): self
    {
        $exception         = new self(sprintf('Method %s not found. The %s method is required on Table classes.', $method, $method));
        $exception->method = $method;

        return $exception;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return [
            'method' => $this->method,
        ];
    }
}
