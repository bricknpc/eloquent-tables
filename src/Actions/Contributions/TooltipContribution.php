<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contributions;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class TooltipContribution extends CapabilityContribution
{
    public function __construct(
        private readonly string $text,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param ActionContext<TModel> $context
     */
    public function renderAttributes(
        ActionDescriptor $descriptor,
        ActionContext $context,
    ): Htmlable|string|\Stringable|View|null {
        // todo use Blade view
        return new HtmlString('data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="' . $this->text . '"');
    }
}
