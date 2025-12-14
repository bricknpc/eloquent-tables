<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

abstract class CapabilityContribution
{
    public function renderBefore(
        ActionDescriptor $descriptor,
        ActionContext $context,
    ): Htmlable|string|\Stringable|View|null {
        return null;
    }

    public function renderAttributes(
        ActionDescriptor $descriptor,
        ActionContext $context,
    ): Htmlable|string|\Stringable|View|null {
        return null;
    }

    public function renderAfter(
        ActionDescriptor $descriptor,
        ActionContext $context,
    ): Htmlable|string|\Stringable|View|null {
        return null;
    }
}
