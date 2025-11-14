<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Factories;

use Illuminate\Contracts\Container\Container;
use BrickNPC\EloquentTables\Contracts\Formatter;

readonly class FormatterFactory
{
    public function __construct(
        private Container $container,
    ) {}

    /**
     * @param class-string<Formatter> $formatter
     * @param array<string, mixed>    $parameters
     */
    public function build(string $formatter, array $parameters = []): Formatter
    {
        /** @var Formatter $formatterObject */
        $formatterObject = $this->container->make($formatter, $parameters);

        return $formatterObject;
    }
}
