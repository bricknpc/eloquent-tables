<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final readonly class LazyValue
{
    /**
     * @template TModel of Model
     *
     * @param null|\Closure(ActionContext<TModel> $context): string|string $value
     */
    public function __construct(
        private \Closure|string|null $value = null,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function resolve(ActionContext $context): string
    {
        if ($this->value === null) {
            return '';
        }

        /** @var string $result */
        $result = is_callable($this->value) ? call_user_func($this->value, $context) : $this->value;

        return $result;
    }
}
