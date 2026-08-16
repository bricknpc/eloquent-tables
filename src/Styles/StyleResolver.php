<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Styles;

use BrickNPC\EloquentTables\Contracts\Style;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\StyleTarget;

readonly class StyleResolver
{
    public function __construct(
        private Config $config,
    ) {}

    /**
     * @param Style[] $styles
     */
    public function classes(array $styles, ?StyleTarget $target = null): string
    {
        $theme   = $this->config->theme();
        $classes = [];

        foreach ($styles as $style) {
            if ($target !== null && $style instanceof CellStyle && $style->target() !== $target) {
                continue;
            }

            $class = $style->toCssClass($theme);

            if ($class !== '') {
                $classes[] = $class;
            }
        }

        return implode(' ', $classes);
    }
}
