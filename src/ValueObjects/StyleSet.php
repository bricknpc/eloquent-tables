<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\ValueObjects;

use BrickNPC\EloquentTables\Contracts\Style;
use BrickNPC\EloquentTables\Contracts\StyleContext;

final readonly class StyleSet
{
    /**
     * @var Style[]
     */
    private array $styles;

    /**
     * @var \Closure[]
     */
    private array $callbacks;

    public function __construct(\Closure|Style ...$styles)
    {
        $collected = [];
        $callbacks = [];

        foreach ($styles as $style) {
            if ($style instanceof \Closure) {
                $callbacks[] = $style;

                continue;
            }

            $collected[] = $style;
        }

        $this->styles    = $collected;
        $this->callbacks = $callbacks;
    }

    public function with(\Closure|Style ...$styles): self
    {
        return new self(...$this->styles, ...$this->callbacks, ...$styles);
    }

    /**
     * @return Style[]
     */
    public function resolve(StyleContext $context): array
    {
        $styles = $this->styles;

        foreach ($this->callbacks as $callback) {
            foreach ($this->toStyles(call_user_func($callback, $context)) as $style) {
                $styles[] = $style;
            }
        }

        return $styles;
    }

    /**
     * @return Style[]
     */
    private function toStyles(mixed $result): array
    {
        if ($result instanceof Style) {
            return [$result];
        }

        if (!is_array($result)) {
            return [];
        }

        return array_values(array_filter($result, static fn (mixed $style) => $style instanceof Style));
    }
}
