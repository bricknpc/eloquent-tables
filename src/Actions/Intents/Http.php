<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Enums\ActionIntentType;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;

final readonly class Http extends ActionIntent
{
    public function __construct(\Closure|string $url, Method $method = Method::Get)
    {
        parent::__construct(ActionIntentType::Http, [
            'url'    => new LazyValue($url),
            'method' => $method->value,
        ]);
    }

    public function view(): string
    {
        return 'eloquent-tables::actions.http';
    }
}
