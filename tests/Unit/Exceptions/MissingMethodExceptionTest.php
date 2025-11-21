<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Exceptions;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Exceptions\MissingMethodException;

/**
 * @internal
 */
#[CoversClass(MissingMethodException::class)]
class MissingMethodExceptionTest extends TestCase
{
    public function test_it_creates_an_exception_with_a_message(): void
    {
        $exception = MissingMethodException::forMethod('invalid');

        $this->assertStringContainsString('Method invalid not found.', $exception->getMessage());
    }

    public function test_it_sets_the_context(): void
    {
        $exception = MissingMethodException::forMethod('invalid');

        $context = $exception->context();

        $this->assertArrayHasKey('method', $context);
        $this->assertSame('invalid', $context['method']);
    }
}
