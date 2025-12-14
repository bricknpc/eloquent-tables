<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

abstract readonly class ActionIntent
{
    abstract public function view(): string;

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function beforeRender(ActionDescriptor $descriptor, ActionContext $context): void
    {
        // This method should be implemented by child classes, but is not required.
    }

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function afterRender(ActionDescriptor $descriptor, ActionContext $context): void
    {
        // This method should be implemented by child classes, but is not required.
    }
}
