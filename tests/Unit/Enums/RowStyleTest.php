<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Enums\RowStyle;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(RowStyle::class)]
class RowStyleTest extends TestCase
{
    #[DataProvider('rowStyleProvider')]
    public function test_it_returns_the_correct_css_class(RowStyle $style, string $expected): void
    {
        $this->assertSame($expected, $style->toCssClass(Theme::Bootstrap5));
    }

    public static function rowStyleProvider(): \Generator
    {
        yield [RowStyle::Primary, 'table-primary'];

        yield [RowStyle::Secondary, 'table-secondary'];

        yield [RowStyle::Tertiary, 'table-tertiary'];

        yield [RowStyle::Quaternary, 'table-quaternary'];

        yield [RowStyle::Success, 'table-success'];

        yield [RowStyle::Warning, 'table-warning'];

        yield [RowStyle::Danger, 'table-danger'];

        yield [RowStyle::Info, 'table-info'];

        yield [RowStyle::Light, 'table-light'];

        yield [RowStyle::Dark, 'table-dark'];
    }

    public function test_it_covers_the_same_palette_as_the_table_vocabulary(): void
    {
        $this->assertCount(10, RowStyle::cases());
    }
}
