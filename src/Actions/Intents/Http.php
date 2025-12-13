<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\ActionIntent;

final readonly class Http extends ActionIntent
{
    public function __construct(
        public \Closure|string $url,
        public Method $method = Method::Get,
    ) {}

    public function view(): string
    {
        return 'eloquent-tables::actions.http';
    }
}
