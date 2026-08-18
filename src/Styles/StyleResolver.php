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
     * @param Style[]     $styles
     * @param CellStyle[] $defaults
     *
     * @return Style[]
     */
    public function withDefaults(array $styles, array $defaults): array
    {
        $declared = [];

        foreach ($styles as $style) {
            if (!$style instanceof CellStyle) {
                continue;
            }

            $declared[] = $style->family();
        }

        foreach ($defaults as $default) {
            if (in_array($default->family(), $declared, true)) {
                continue;
            }

            $styles[] = $default;
        }

        return $styles;
    }

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
