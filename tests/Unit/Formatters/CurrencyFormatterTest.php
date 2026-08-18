<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Formatters;

use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;

/**
 * @internal
 */
#[CoversClass(CurrencyFormatter::class)]
class CurrencyFormatterTest extends TestCase
{
    #[DataProvider('currencyProvider')]
    public function test_it_formats_currency_for_locale(
        string $locale,
        string $currency,
        mixed $value,
        string $expected,
    ): void {
        $formatter = new CurrencyFormatter($locale, $currency);

        $model = new class extends Model {};

        $formatted = $formatter->format($value, $model);

        $this->assertSame($expected, $formatted->__toString());
    }

    public static function currencyProvider(): \Generator
    {
        yield [
            'en',
            'EUR',
            91.23,
            '€91.23',
        ];

        yield [
            'nl',
            'EUR',
            91.23,
            "€\xC2\xA091,23",
        ];

        yield [
            'de',
            'EUR',
            91.23,
            "91,23\xC2\xA0€",
        ];

        yield [
            'en',
            'USD',
            91.23,
            '$91.23',
        ];

        yield [
            'nl',
            'USD',
            91.23,
            "US$\xC2\xA091,23",
        ];

        yield [
            'de',
            'USD',
            91.23,
            "91,23\xC2\xA0$",
        ];
    }

    public function test_it_formats_the_same_value_with_and_without_a_model(): void
    {
        $formatter = new CurrencyFormatter('en_US', 'USD');

        $this->assertSame((string) $formatter->format(1234.56, new TestModel()), (string) $formatter->format(1234.56));
    }

    public function test_it_formats_a_value_with_no_model_at_all(): void
    {
        $this->assertNotSame('', (string) new CurrencyFormatter('en_US', 'USD')->format(1234.56));
    }
}
