<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final readonly class LazyValue
{
    /**
     * @param null|\Closure(ActionContext $context): string|string $value
     */
    public function __construct(
        private \Closure|string|null $value = null,
    ) {}

    public function resolve(ActionContext $context): ?string
    {
        if ($this->value === null) {
            return null;
        }

        // Not is_callable: a label like "Log" or "Key" names a PHP function, and function names are
        // case-insensitive, so is_callable would call it instead of returning the label.
        /** @var string $result */
        $result = $this->value instanceof \Closure ? call_user_func($this->value, $context) : $this->value;

        return $result;
    }
}
