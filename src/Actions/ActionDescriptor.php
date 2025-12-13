<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;

final class ActionDescriptor
{
    public LazyValue $label;

    /**
     * @var array<string, string>
     */
    public array $attributes = [];

    public string $beforeRender       = '';
    public string $afterRender        = '';
    public string $attributesRendered = '';

    public ?ActionIntent $intent = null;

    public function __construct()
    {
        $this->label = new LazyValue('');
    }
}
