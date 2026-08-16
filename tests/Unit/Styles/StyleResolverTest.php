<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Styles;

use BrickNPC\EloquentTables\Enums\RowStyle;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use BrickNPC\EloquentTables\Styles\StyleResolver;

/**
 * @internal
 */
#[CoversClass(StyleResolver::class)]
#[UsesClass(Config::class)]
#[UsesClass(CellStyle::class)]
#[UsesClass(RowStyle::class)]
class StyleResolverTest extends TestCase
{
    public function test_it_joins_the_classes_of_every_style(): void
    {
        $this->assertSame(
            'table-success fw-bold',
            $this->resolver()->classes([CellStyle::BackgroundSuccess, CellStyle::FontBold]),
        );
    }

    public function test_it_keeps_only_styles_matching_the_target(): void
    {
        $styles = [CellStyle::AlignRight, CellStyle::BackgroundSuccess];

        $resolver = $this->resolver();

        $this->assertSame('table-success', $resolver->classes($styles, StyleTarget::Cell));
        $this->assertSame('justify-content-end', $resolver->classes($styles, StyleTarget::Content));
    }

    public function test_a_style_outside_the_cell_vocabulary_ignores_the_target(): void
    {
        $this->assertSame(
            'table-danger',
            $this->resolver()->classes([RowStyle::Danger], StyleTarget::Cell),
        );
    }

    public function test_no_styles_yields_an_empty_string(): void
    {
        $this->assertSame('', $this->resolver()->classes([]));
    }

    private function resolver(): StyleResolver
    {
        /** @var StyleResolver $resolver */
        $resolver = $this->app->make(StyleResolver::class);

        return $resolver;
    }
}
