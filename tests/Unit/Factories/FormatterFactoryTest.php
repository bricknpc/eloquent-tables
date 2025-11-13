<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Factories;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Formatters\DateFormatter;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;
use BrickNPC\EloquentTables\Formatters\DateTimeFormatter;

/**
 * @internal
 */
#[CoversClass(FormatterFactory::class)]
#[UsesClass(DateFormatter::class)]
#[UsesClass(DateTimeFormatter::class)]
#[UsesClass(NumberFormatter::class)]
#[UsesClass(CurrencyFormatter::class)]
class FormatterFactoryTest extends TestCase
{
    #[DataProvider('formatterInstanceProvider')]
    public function test_it_can_create_a_formatter_instance(string $formatter): void
    {
        $factory = new FormatterFactory(app());

        $result = $factory->build($formatter);

        $this->assertInstanceOf($formatter, $result);
    }

    public static function formatterInstanceProvider(): \Generator
    {
        yield [
            DateFormatter::class,
        ];

        yield [
            DateTimeFormatter::class,
        ];

        yield [
            NumberFormatter::class,
        ];

        yield [
            CurrencyFormatter::class,
        ];
    }
}
