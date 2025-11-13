<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(TableStyle::class)]
class TableStyleTest extends TestCase
{
    #[DataProvider('tableStyleProvider')]
    public function test_it_returns_to_correct_css_class(Theme $theme, TableStyle $style, string $expected): void
    {
        $result = $style->toCssClass($theme);

        $this->assertSame($expected, $result);
    }

    public static function tableStyleProvider(): \Generator
    {
        yield [
            Theme::Bootstrap5,
            TableStyle::Default,
            '',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Primary,
            'table-primary',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Secondary,
            'table-secondary',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Tertiary,
            'table-tertiary',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Quaternary,
            'table-quaternary',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Success,
            'table-success',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Warning,
            'table-warning',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Danger,
            'table-danger',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Info,
            'table-info',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Light,
            'table-light',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Dark,
            'table-dark',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Striped,
            'table-striped',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Hover,
            'table-hover',
        ];

        yield [
            Theme::Bootstrap5,
            TableStyle::Active,
            'table-active',
        ];
    }
}
