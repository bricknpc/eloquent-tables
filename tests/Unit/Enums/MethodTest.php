<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Enums;

use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversClass(Method::class)]
class MethodTest extends TestCase
{
    #[DataProvider('methodProvider')]
    public function test_it_returns_the_correct_form_method(Method $method, string $formMethod): void
    {
        $this->assertSame($formMethod, $method->getFormMethod());
    }

    public static function methodProvider(): \Generator
    {
        yield [
            Method::Get,
            'GET',
        ];

        yield [
            Method::Post,
            'POST',
        ];

        yield [
            Method::Put,
            'POST',
        ];

        yield [
            Method::Patch,
            'POST',
        ];

        yield [
            Method::Delete,
            'POST',
        ];
    }
}
