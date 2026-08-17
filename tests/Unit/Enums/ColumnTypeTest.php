<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Tests\TestCase;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Enums\ColumnType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(ColumnType::class)]
class ColumnTypeTest extends TestCase
{
    #[DataProvider('typeProvider')]
    public function test_it_returns_the_correct_view(ColumnType $type, string $view): void
    {
        $this->assertSame($view, $type->getTdView());
    }

    public static function typeProvider(): \Generator
    {
        yield [
            ColumnType::Text,
            'td-text',
        ];

        yield [
            ColumnType::Boolean,
            'td-boolean',
        ];

        yield [
            ColumnType::Checkbox,
            'td-checkbox',
        ];
    }

    #[DataProvider('typeHeaderProvider')]
    public function test_it_returns_the_correct_header_view(ColumnType $type, string $view): void
    {
        $this->assertSame($view, $type->getThView());
    }

    public static function typeHeaderProvider(): \Generator
    {
        yield [
            ColumnType::Text,
            'th-text',
        ];

        yield [
            ColumnType::Boolean,
            'th-boolean',
        ];

        yield [
            ColumnType::Checkbox,
            'th-checkbox',
        ];
    }

    public function test_a_text_column_contributes_no_default_style(): void
    {
        $this->assertSame([], ColumnType::Text->defaultStyles());
    }

    public function test_a_boolean_column_defaults_to_centred(): void
    {
        $this->assertSame([CellStyle::AlignCenter], ColumnType::Boolean->defaultStyles());
    }

    public function test_a_checkbox_column_defaults_to_centred(): void
    {
        $this->assertSame([CellStyle::AlignCenter], ColumnType::Checkbox->defaultStyles());
    }
}
