<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contributions;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class ConfirmationContribution extends CapabilityContribution
{
    public function __construct(
        private readonly string $text,
        private readonly ?string $confirmationText = null,
        private readonly ?string $cancelText = null,
        private readonly ?string $understandValue = null,
    ) {}

    public function renderAttributes(ActionDescriptor $descriptor, ActionContext $context): Htmlable|string|\Stringable|View|null
    {
        // todo this should load the view, which is again based on the context of the config (theme) so we don't write bootstrap html code here
        return new HtmlString('data-et-confirm="true" data-et-confirm-target="#row-action-unique-id-modal"');
    }

    public function renderAfter(ActionDescriptor $descriptor, ActionContext $context): Htmlable|string|\Stringable|View|null
    {
        // todo this should load the view, which is again based on the context of the config (theme) so we don't write bootstrap html code here
        return new HtmlString('');
    }
}
