<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Formatters;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Exceptions\InvalidValueException;

/**
 * @internal
 */
#[CoversClass(NumberFormatter::class)]
#[UsesClass(InvalidValueException::class)]
class NumberFormatterTest extends TestCase
{
    #[DataProvider('numberProvider')]
    public function test_it_formats_currency_for_locale(
        string $locale,
        int $decimals,
        mixed $value,
        string $expected,
    ): void {
        $formatter = new NumberFormatter($locale, $decimals);

        $model = new class extends Model {};

        $formatted = $formatter->format($value, $model);

        $this->assertSame($expected, $formatted->__toString());
    }

    public static function numberProvider(): \Generator
    {
        yield [
            'en',
            2,
            12.3456,
            '12.35',
        ];

        yield [
            'nl',
            2,
            12.3456,
            '12,35',
        ];

        yield [
            'en',
            2,
            1212.3456,
            '1,212.35',
        ];

        yield [
            'nl',
            2,
            1212.3456,
            '1.212,35',
        ];

        yield [
            'en',
            2,
            -1212.3456,
            '-1,212.35',
        ];

        yield [
            'nl',
            2,
            -1212.3456,
            '-1.212,35',
        ];

        yield [
            'en',
            0,
            12.3456,
            '12',
        ];

        yield [
            'nl',
            0,
            12.3456,
            '12',
        ];

        yield [
            'en',
            0,
            1212.3456,
            '1,212',
        ];

        yield [
            'nl',
            0,
            1212.3456,
            '1.212',
        ];

        yield [
            'en',
            0,
            -1212.3456,
            '-1,212',
        ];

        yield [
            'nl',
            0,
            -1212.3456,
            '-1.212',
        ];

        yield [
            'en',
            4,
            0,
            '0.0000',
        ];

        yield [
            'nl',
            4,
            0,
            '0,0000',
        ];
    }

    #[DataProvider('invalidValueProvider')]
    public function test_non_numeric_value_throws_exception(string $locale, int $decimals, mixed $value): void
    {
        $formatter = new NumberFormatter($locale, $decimals);

        $model = new class extends Model {};

        $this->expectException(InvalidValueException::class);

        $formatter->format($value, $model);
    }

    public static function invalidValueProvider(): \Generator
    {
        yield [
            'en',
            2,
            'Invalid',
        ];

        yield [
            'en',
            2,
            null,
        ];

        yield [
            'en',
            2,
            static function () {},
        ];

        yield [
            'en',
            2,
            new class {},
        ];

        yield [
            'en',
            2,
            static fn() => true,
        ];

        yield [
            'en',
            2,
            [],
        ];

        yield [
            'en',
            2,
            true,
        ];

        yield [
            'en',
            2,
            false,
        ];
    }

    public function test_it_formats_the_same_value_with_and_without_a_model(): void
    {
        $formatter = new NumberFormatter('en_US');

        $this->assertSame((string) $formatter->format(1234.56, new TestModel()), (string) $formatter->format(1234.56));
    }

    public function test_it_formats_a_value_with_no_model_at_all(): void
    {
        $this->assertNotSame('', (string) new NumberFormatter('en_US')->format(1234.56));
    }
}
