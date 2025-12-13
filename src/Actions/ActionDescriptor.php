<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

final class ActionDescriptor
{
    public LazyValue $label;

    /**
     * @var array<string, string>
     */
    public array $attributes = [];

    public RenderBuffer $beforeRender;
    public RenderBuffer $afterRender;
    public RenderBuffer $attributesRender;

    public ?ActionIntent $intent = null;

    public function __construct()
    {
        $this->label            = new LazyValue('');
        $this->beforeRender     = new RenderBuffer();
        $this->afterRender      = new RenderBuffer();
        $this->attributesRender = new RenderBuffer();
    }
}
