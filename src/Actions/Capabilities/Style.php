<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\Capabilities;

use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final class Style extends ActionCapability
{
    /**
     * @var ButtonStyle[]
     */
    private readonly array $styles;

    public function __construct(ButtonStyle ...$styles)
    {
        $this->styles = $styles;
    }

    public function apply(ActionDescriptor $descriptor, ActionContext $context): void
    {
        $classes = array_filter(array_map(
            fn (ButtonStyle $style) => $style->toCssClass($context->config->theme(), $context->asDropdown),
            $this->styles,
        ));

        if ($classes === []) {
            return;
        }

        $descriptor->attributes['class'] = implode(' ', $classes);
    }
}
