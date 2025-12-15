<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

abstract class ActionIntent
{
    /**
     * @var null|\Closure(ActionDescriptor, ActionContext): void
     */
    protected ?\Closure $before = null;

    /**
     * @var null|\Closure(ActionDescriptor, ActionContext): void
     */
    protected ?\Closure $after = null;

    abstract public function view(): string;

    /**
     * @param \Closure(ActionDescriptor $descriptor, ActionContext $context): void $before
     */
    public function before(\Closure $before): static
    {
        $this->before = $before;

        return $this;
    }

    public function beforeRender(ActionDescriptor $descriptor, ActionContext $context): void
    {
        if ($this->before !== null) {
            call_user_func($this->before, $descriptor, $context);
        }
    }

    /**
     * @param \Closure(ActionDescriptor $descriptor, ActionContext $context): void $after
     */
    public function after(\Closure $after): static
    {
        $this->after = $after;

        return $this;
    }

    public function afterRender(ActionDescriptor $descriptor, ActionContext $context): void
    {
        if ($this->after !== null) {
            call_user_func($this->after, $descriptor, $context);
        }
    }
}
