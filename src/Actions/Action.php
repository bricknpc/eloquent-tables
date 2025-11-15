<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions;

use BrickNPC\EloquentTables\Enums\ButtonStyle;

abstract class Action
{
    /**
     * @param ButtonStyle[] $styles
     */
    public function __construct(
        public string|\Stringable|null $label = null,
        public array $styles = [],
    ) {}

    public function label(string|\Stringable $label): self
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
