<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final readonly class LazyValue
{
    /**
     * @param \Closure(ActionContext $context): string|string $value
     */
    public function __construct(
        private \Closure|string $value,
    ) {}

    public function resolve(ActionContext $context): string
    {
        return is_callable($this->value) ? call_user_func($this->value, $context) : (string) $this->value;
    }
}
