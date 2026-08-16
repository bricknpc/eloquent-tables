<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Styles;

use BrickNPC\EloquentTables\Enums\RowStyle;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\AccentStyle;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use BrickNPC\EloquentTables\Styles\StyleResolver;

/**
 * @internal
 */
#[CoversClass(StyleResolver::class)]
#[UsesClass(Config::class)]
#[UsesClass(CellStyle::class)]
#[UsesClass(RowStyle::class)]
#[UsesClass(AccentStyle::class)]
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

    public function test_a_default_applies_when_its_family_is_absent(): void
    {
        $styles = $this->resolver()->withDefaults([CellStyle::BackgroundSuccess], [CellStyle::AlignCenter]);

        $this->assertSame([CellStyle::BackgroundSuccess, CellStyle::AlignCenter], $styles);
    }

    public function test_a_default_is_displaced_by_a_declared_style_of_the_same_family(): void
    {
        $styles = $this->resolver()->withDefaults([CellStyle::AlignRight], [CellStyle::AlignCenter]);

        $this->assertSame([CellStyle::AlignRight], $styles);
    }

    public function test_no_defaults_leaves_the_styles_alone(): void
    {
        $this->assertSame([CellStyle::AlignRight], $this->resolver()->withDefaults([CellStyle::AlignRight], []));
    }

    public function test_a_style_outside_the_cell_vocabulary_does_not_displace_a_default(): void
    {
        $styles = $this->resolver()->withDefaults([RowStyle::Danger], [CellStyle::AlignCenter]);

        $this->assertSame([RowStyle::Danger, CellStyle::AlignCenter], $styles);
    }

    public function test_the_accent_is_the_last_one_declared(): void
    {
        $this->assertSame(
            AccentStyle::Success,
            $this->resolver()->accent([AccentStyle::Danger, AccentStyle::Success]),
        );
    }

    public function test_the_accent_defaults_to_primary_when_none_is_declared(): void
    {
        $this->assertSame(AccentStyle::Primary, $this->resolver()->accent([]));
    }

    public function test_a_style_from_another_vocabulary_is_not_an_accent(): void
    {
        $this->assertSame(
            AccentStyle::Primary,
            $this->resolver()->accent([CellStyle::BackgroundSuccess, RowStyle::Danger]),
        );
    }

    private function resolver(): StyleResolver
    {
        /** @var StyleResolver $resolver */
        $resolver = $this->app->make(StyleResolver::class);

        return $resolver;
    }
}
