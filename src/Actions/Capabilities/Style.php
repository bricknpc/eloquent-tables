<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class Style extends ActionCapability
{
    private readonly StyleSet $styles;

    public function __construct(ButtonStyle|\Closure ...$styles)
    {
        $this->styles = new StyleSet(...$styles);
    }

    public function apply(ActionDescriptor $descriptor, ActionContext $context): void
    {
        $styles = array_filter(
            $this->styles->resolve($context),
            fn (mixed $style) => $style instanceof ButtonStyle,
        );

        $classes = array_filter(array_map(
            fn (ButtonStyle $style) => $style->toCssClass($context->config->theme(), $context->asDropdown),
            $styles,
        ));

        if ($classes === []) {
            return;
        }

        $descriptor->attributes['class'] = implode(' ', $classes);
    }
}
