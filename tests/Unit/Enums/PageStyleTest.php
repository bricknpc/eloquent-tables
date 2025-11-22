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
}
