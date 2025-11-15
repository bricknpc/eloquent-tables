<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use Illuminate\Contracts\Support\Htmlable;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

abstract class Action
{
    /**
     * @param ButtonStyle[] $styles
     */
    public function __construct(
        public Htmlable|string|\Stringable|null $label = null,
        public array $styles = [],
    ) {}

    public function label(Htmlable|string|\Stringable $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function styles(ButtonStyle ...$styles): self
    {
        $this->styles = array_merge($this->styles, $styles);

        return $this;
    }
}
