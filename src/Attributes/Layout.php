<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Layout
{
    public function __construct(
        public string $name,
        public ?string $section = null,
        public array $with = [],
    ) {}
}
