<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use BrickNPC\EloquentTables\Enums\PageStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(PageStyle::class)]
class PageStyleTest extends TestCase
{
    #[DataProvider('pageStyleProvider')]
    public function test_it_returns_to_correct_css_class(Theme $theme, PageStyle $style, string $expected): void
    {
        $result = $style->toCssClass($theme);

        $this->assertSame($expected, $result);
    }

    public static function pageStyleProvider(): \Generator
    {
        yield [
            Theme::Bootstrap5,
            PageStyle::Primary,
            'primary',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Secondary,
            'secondary',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Tertiary,
            'tertiary',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Quaternary,
            'quaternary',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Success,
            'success',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Warning,
            'warning',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Danger,
            'danger',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Info,
            'info',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Light,
            'light',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Dark,
            'dark',
        ];
    }

    #[DataProvider('pageStyleActiveProvider')]
    public function test_it_returns_to_correct_active_css_class(Theme $theme, PageStyle $style, string $expected): void
    {
        $result = $style->toCssActiveClass($theme);

        $this->assertSame($expected, $result);
    }

    public static function pageStyleActiveProvider(): \Generator
    {
        yield [
            Theme::Bootstrap5,
            PageStyle::Primary,
            'light',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Secondary,
            'light',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Tertiary,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Quaternary,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Success,
            'light',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Warning,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Danger,
            'light',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Info,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Light,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Dark,
            'light',
        ];
    }

    #[DataProvider('pageStyleDisabledProvider')]
    public function test_it_returns_to_correct_disabled_css_class(Theme $theme, PageStyle $style, string $expected): void
    {
        $result = $style->toCssDisabledClass($theme);

        $this->assertSame($expected, $result);
    }

    public static function pageStyleDisabledProvider(): \Generator
    {
        yield [
            Theme::Bootstrap5,
            PageStyle::Primary,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Secondary,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Tertiary,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Quaternary,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Success,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Warning,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Danger,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Info,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Light,
            'dark',
        ];

        yield [
            Theme::Bootstrap5,
            PageStyle::Dark,
            'dark',
        ];
    }
}
