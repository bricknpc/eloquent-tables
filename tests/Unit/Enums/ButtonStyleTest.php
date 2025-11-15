<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(ButtonStyle::class)]
class ButtonStyleTest extends TestCase
{
    #[DataProvider('buttonStyleProvider')]
    public function test_it_returns_to_correct_css_class(Theme $theme, ButtonStyle $style, string $expected): void
    {
        $result = $style->toCssClass($theme);

        $this->assertSame($expected, $result);
    }

    public static function buttonStyleProvider(): \Generator
    {
        yield [
            Theme::Bootstrap5,
            ButtonStyle::Default,
            '',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Primary,
            'btn-primary',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::PrimaryOutline,
            'btn-outline-primary',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Secondary,
            'btn-secondary',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::SecondaryOutline,
            'btn-outline-secondary',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Tertiary,
            'btn-tertiary',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::TertiaryOutline,
            'btn-outline-tertiary',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Quaternary,
            'btn-quaternary',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::QuaternaryOutline,
            'btn-outline-quaternary',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Success,
            'btn-success',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::SuccessOutline,
            'btn-outline-success',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Warning,
            'btn-warning',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::WarningOutline,
            'btn-outline-warning',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Danger,
            'btn-danger',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::DangerOutline,
            'btn-outline-danger',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Info,
            'btn-info',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::InfoOutline,
            'btn-outline-info',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Link,
            'btn-link',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Light,
            'btn-light',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::LightOutline,
            'btn-outline-light',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::Dark,
            'btn-dark',
        ];

        yield [
            Theme::Bootstrap5,
            ButtonStyle::DarkOutline,
            'btn-outline-dark',
        ];
    }
}
