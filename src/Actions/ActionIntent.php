<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

abstract readonly class ActionIntent
{
    abstract public function view(): string;

    public function beforeRender(ActionDescriptor $descriptor, ActionContext $context): void
    {
        // This method should be implemented by child classes, but is not required.
    }

    public function afterRender(ActionDescriptor $descriptor, ActionContext $context): void
    {
        // This method should be implemented by child classes, but is not required.
    }
}
