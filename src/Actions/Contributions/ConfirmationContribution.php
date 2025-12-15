<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Contributions;

use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @todo This class isn't implemented yet, this is just a placeholder, so lets ignore PHPStand for now
 */
final class ConfirmationContribution extends CapabilityContribution
{
    public function __construct(
        private readonly string $text, // @phpstan-ignore-line
        private readonly ?string $confirmationText = null, // @phpstan-ignore-line
        private readonly ?string $cancelText = null, // @phpstan-ignore-line
        private readonly ?string $understandValue = null, // @phpstan-ignore-line
    ) {}

    public function renderAttributes(ActionDescriptor $descriptor, ActionContext $context): Htmlable|string|\Stringable|View  // @phpstan-ignore-line
    {
        // todo this should load the view, which is again based on the context of the config (theme) so we don't write bootstrap html code here
        return new HtmlString('data-et-confirm="true" data-et-confirm-target="#row-action-unique-id-modal"');
    }

    public function renderAfter(ActionDescriptor $descriptor, ActionContext $context): Htmlable|string|\Stringable|View // @phpstan-ignore-line
    {
        // todo this should load the view, which is again based on the context of the config (theme) so we don't write bootstrap html code here
        return new HtmlString('');
    }
}
