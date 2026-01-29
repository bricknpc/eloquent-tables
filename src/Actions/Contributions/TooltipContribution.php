<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contributions;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class TooltipContribution extends CapabilityContribution
{
    private readonly string $id;

    public function __construct(
        private readonly string $text,
    ) {
        $this->id = md5(uniqid(more_entropy: true));
    }

    public function renderAttributes(
        ActionDescriptor $descriptor,
        ActionContext $context,
    ): Htmlable|string|\Stringable|View|null {
        return \view('eloquent-tables::actions.contribution.tooltip-attributes', [
            'text' => $this->text,
            'theme' => $context->config->theme(),
            'id' => $this->id,
            'dataNamespace' => $context->config->dataNamespace(),
        ]);
    }
}
