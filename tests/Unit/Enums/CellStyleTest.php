<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use BrickNPC\EloquentTables\Enums\CellStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(CellStyle::class)]
class CellStyleTest extends TestCase
{
    #[DataProvider('cellStyleProvider')]
    public function test_it_returns_to_correct_css_class(Theme $theme, CellStyle $style, bool $flex, string $expected): void
    {
        $result = $style->toCssClass($theme, $flex);

        $this->assertSame($expected, $result);
    }

    public static function cellStyleProvider(): \Generator
    {
        // Bootstrap 5 - Non-flex mode
        yield [
            Theme::Bootstrap5,
            CellStyle::AlignLeft,
            false,
            'text-start',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignCenter,
            false,
            'text-center',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignRight,
            false,
            'text-end',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignJustify,
            false,
            'text-justify',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignBetween,
            false,
            '',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignTop,
            false,
            'align-text-top',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignMiddle,
            false,
            'align-middle',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignBottom,
            false,
            'align-text-bottom',
        ];

        // Bootstrap 5 - Flex mode
        yield [
            Theme::Bootstrap5,
            CellStyle::AlignLeft,
            true,
            'justify-content-start',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignCenter,
            true,
            'justify-content-center',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignRight,
            true,
            'justify-content-end',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignJustify,
            true,
            'justify-content-stretch',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignBetween,
            true,
            'justify-content-between',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignTop,
            true,
            'align-items-start',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignMiddle,
            true,
            'align-items-center',
        ];

        yield [
            Theme::Bootstrap5,
            CellStyle::AlignBottom,
            true,
            'align-items-end',
        ];
    }
}
