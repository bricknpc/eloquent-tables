<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Actions\ActionIntent;

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
}
