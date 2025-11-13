<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Exceptions;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Exceptions\InvalidValueException;

/**
 * @internal
 */
#[CoversClass(InvalidValueException::class)]
#[UsesClass(NumberFormatter::class)]
class InvalidValueExceptionTest extends TestCase
{
    #[DataProvider('invalidValueProvider')]
    public function test_it_creates_an_exception_with_a_message(mixed $value, string $expectedInMessage): void
    {
        $exception = InvalidValueException::forInvalidValue($value, new NumberFormatter('en', 2));

        $this->assertStringContainsString($expectedInMessage, $exception->getMessage());
    }

    public static function invalidValueProvider(): \Generator
    {
        yield [
            'Invalid',
            'Invalid',
        ];

        yield [
            null,
            'null',
        ];

        yield [
            function () {},
            'of type Closure',
        ];

        yield [
            collect(),
            'of type Illuminate\Support\Collection',
        ];

        yield [
            [],
            'of type array',
        ];
    }

    #[DataProvider('invalidValueProvider')]
    public function test_it_saves_the_invalid_value_formatter_and_adds_them_to_the_context(mixed $value, string $other): void
    {
        $exception = InvalidValueException::forInvalidValue($value, new NumberFormatter('en', 2));

        $this->assertSame($value, $exception->value);
        $this->assertInstanceOf(NumberFormatter::class, $exception->formatter);

        $context = $exception->context();

        $this->assertArrayHasKey('invalid_value', $context);
        $this->assertArrayHasKey('formatter', $context);
        $this->assertSame($value, $context['invalid_value']);
        $this->assertInstanceOf(NumberFormatter::class, $context['formatter']);
    }
}
