<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Intents;

use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;

final class Http extends ActionIntent
{
    public function __construct(
        public readonly \Closure|string $url,
        public readonly Method $method = Method::Get,
    ) {}

    public function view(): string
    {
        return 'eloquent-tables::actions.http';
    }

    public function url(): LazyValue
    // @mago-expect analysis:less-specific-nested-argument-type -- LazyValue's template is invariant, so the narrower value type is rejected
    {
        return new LazyValue($this->url);
    }
}
