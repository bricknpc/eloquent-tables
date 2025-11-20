<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Tests\TestCase;
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
}
