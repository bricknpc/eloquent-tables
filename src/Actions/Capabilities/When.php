<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class When extends ActionCapability
{
    /**
     * @template TModel of Model
     *
     * @param \Closure(ActionContext<TModel> $context): bool $condition
     */
    public function __construct(
        private readonly \Closure $condition,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function check(ActionDescriptor $descriptor, ActionContext $context): bool
    {
        return call_user_func($this->condition, $context);
    }
}
