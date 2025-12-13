<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;

final class ActionDescriptor
{
    public LazyValue $label;
    public string $element = 'button'; // to, change to enum

    /**
     * @var array<string, string>
     */
    public array $attributes = [];

    public ?ActionIntent $intent = null;

    public function __construct()
    {
        $this->label = new LazyValue('');
    }
}
