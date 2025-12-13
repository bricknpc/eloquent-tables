<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Actions\ActionIntent;

final readonly class HttpModal extends ActionIntent
{
    public function __construct(
        public \Closure|string $title,
        public \Closure|string $url,
    ) {}

    public function view(): string
    {
        return 'eloquent-tables::actions.http-modal';
    }
}
