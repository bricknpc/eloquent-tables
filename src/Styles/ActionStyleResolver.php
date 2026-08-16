<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Styles;

use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Enums\ActionRegion;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

final readonly class ActionStyleResolver
{
    public function classes(?StyleSet $styles, ActionContext $context, ActionRegion $region): string
    {
        $theme = $context->config->theme();

        $variants = array_filter(array_map(
            fn (ButtonStyle $style) => $style->toCssClass($theme, $region),
            array_filter(
                $styles?->resolve($context) ?? [],
                fn (mixed $style) => $style instanceof ButtonStyle,
            ),
        ));

        if ($variants === []) {
            $variants = array_filter([$region->defaultStyle()?->toCssClass($theme, $region)]);
        }

        return implode(' ', [$region->baseCssClass($theme), ...$variants]);
    }
}
