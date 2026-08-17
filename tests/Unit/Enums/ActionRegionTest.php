<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Enums\ActionRegion;

/**
 * @internal
 */
#[CoversClass(ActionRegion::class)]
class ActionRegionTest extends TestCase
{
    #[DataProvider('baseCssClassProvider')]
    public function test_it_returns_the_correct_base_css_class(
        Theme $theme,
        ActionRegion $region,
        string $expected,
    ): void {
        $this->assertSame($expected, $region->baseCssClass($theme));
    }

    public static function baseCssClassProvider(): \Generator
    {
        yield [
            Theme::Bootstrap5,
            ActionRegion::Button,
            'btn',
        ];

        yield [
            Theme::Bootstrap5,
            ActionRegion::DropdownItem,
            'dropdown-item',
        ];

        yield [
            Theme::Bootstrap5,
            ActionRegion::DropdownToggle,
            'btn dropdown-toggle',
        ];
    }

    #[DataProvider('defaultStyleProvider')]
    public function test_it_returns_the_correct_default_style(ActionRegion $region, ?ButtonStyle $expected): void
    {
        $this->assertSame($expected, $region->defaultStyle());
    }

    public static function defaultStyleProvider(): \Generator
    {
        yield [
            ActionRegion::Button,
            ButtonStyle::Primary,
        ];

        yield 'a dropdown item falls back to no variant at all' => [
            ActionRegion::DropdownItem,
            null,
        ];

        yield [
            ActionRegion::DropdownToggle,
            ButtonStyle::Primary,
        ];
    }

    public function test_every_region_has_a_base_css_class(): void
    {
        foreach (ActionRegion::cases() as $region) {
            $this->assertNotSame('', $region->baseCssClass(Theme::Bootstrap5));
        }
    }

    public function test_it_has_a_region_for_every_place_an_action_renders(): void
    {
        $this->assertCount(3, ActionRegion::cases());
    }
}
