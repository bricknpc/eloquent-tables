<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;

final class Modal extends ActionIntent
{
    public function __construct(
        public readonly \Closure|string $title,
        public readonly \Closure|string|null $content = null,
    ) {}

    public function view(): string
    {
        return 'eloquent-tables::actions.modal';
    }

    public function title(): LazyValue
    // @mago-expect analysis:less-specific-nested-argument-type -- LazyValue's template is invariant, so the narrower value type is rejected
    {
        return new LazyValue($this->title);
    }

    public function content(): LazyValue
    // @mago-expect analysis:less-specific-nested-argument-type -- LazyValue's template is invariant, so the narrower value type is rejected
    {
        return new LazyValue($this->content);
    }
}
