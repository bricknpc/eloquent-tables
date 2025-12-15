<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\ValueObjects;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @internal
 */
#[CoversClass(LazyValue::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Config::class)]
class LazyValueTest extends TestCase
{
    public function test_resolve_returns_empty_string_when_value_is_null(): void
    {
        $lazyValue = new LazyValue(null);
        $context   = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('', $result);
    }

    public function test_resolve_returns_empty_string_when_no_value_provided(): void
    {
        $lazyValue = new LazyValue();
        $context   = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('', $result);
    }

    public function test_resolve_returns_string_when_value_is_string(): void
    {
        $lazyValue = new LazyValue('Hello World');
        $context   = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('Hello World', $result);
    }

    public function test_resolve_returns_empty_string_when_value_is_empty_string(): void
    {
        $lazyValue = new LazyValue('');
        $context   = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('', $result);
    }

    public function test_resolve_returns_numeric_string(): void
    {
        $lazyValue = new LazyValue('12345');
        $context   = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('12345', $result);
    }

    public function test_resolve_executes_closure_and_returns_result(): void
    {
        $lazyValue = new LazyValue(function (ActionContext $context): string {
            return 'Closure Result';
        });
        $context = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('Closure Result', $result);
    }

    public function test_resolve_passes_context_to_closure(): void
    {
        $context       = $this->createActionContext();
        $contextPassed = null;

        $lazyValue = new LazyValue(function (ActionContext $ctx) use (&$contextPassed): string {
            $contextPassed = $ctx;

            return 'test';
        });

        $lazyValue->resolve($context);

        $this->assertSame($context, $contextPassed);
    }

    public function test_resolve_can_be_called_multiple_times_with_string_value(): void
    {
        $lazyValue = new LazyValue('Static Value');
        $context   = $this->createActionContext();

        $result1 = $lazyValue->resolve($context);
        $result2 = $lazyValue->resolve($context);

        $this->assertSame('Static Value', $result1);
        $this->assertSame('Static Value', $result2);
        $this->assertSame($result1, $result2);
    }

    public function test_resolve_can_be_called_multiple_times_with_closure(): void
    {
        $callCount = 0;
        $context   = $this->createActionContext();

        $lazyValue = new LazyValue(function (ActionContext $context) use (&$callCount): string {
            ++$callCount;

            return 'Call ' . $callCount;
        });

        $result1 = $lazyValue->resolve($context);
        $result2 = $lazyValue->resolve($context);

        $this->assertSame('Call 1', $result1);
        $this->assertSame('Call 2', $result2);
        $this->assertSame(2, $callCount);
    }

    public function test_resolve_closure_returns_empty_string(): void
    {
        $lazyValue = new LazyValue(function (ActionContext $context): string {
            return '';
        });
        $context = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('', $result);
    }

    public function test_resolve_closure_returns_multiline_string(): void
    {
        $lazyValue = new LazyValue(function (ActionContext $context): string {
            return "Line 1\nLine 2\nLine 3";
        });
        $context = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame("Line 1\nLine 2\nLine 3", $result);
    }

    public function test_resolve_closure_returns_string_with_special_characters(): void
    {
        $lazyValue = new LazyValue(function (ActionContext $context): string {
            return '<div>Special & "quoted" \'content\'</div>';
        });
        $context = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('<div>Special & "quoted" \'content\'</div>', $result);
    }

    public function test_resolve_with_different_contexts(): void
    {
        $context1 = $this->createActionContext();
        $context2 = $this->createActionContext();

        $lazyValue = new LazyValue(function (ActionContext $context): string {
            return 'Result from context: ' . spl_object_id($context);
        });

        $result1 = $lazyValue->resolve($context1);
        $result2 = $lazyValue->resolve($context2);

        $this->assertStringContainsString('Result from context:', $result1);
        $this->assertStringContainsString('Result from context:', $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function test_class_is_readonly(): void
    {
        $reflection = new \ReflectionClass(LazyValue::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_class_is_final(): void
    {
        $reflection = new \ReflectionClass(LazyValue::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_resolve_with_closure_that_uses_external_variables(): void
    {
        $externalValue = 'External';
        $context       = $this->createActionContext();

        $lazyValue = new LazyValue(function (ActionContext $context) use ($externalValue): string {
            return $externalValue . ' Value';
        });

        $result = $lazyValue->resolve($context);

        $this->assertSame('External Value', $result);
    }

    public function test_resolve_string_with_whitespace(): void
    {
        $lazyValue = new LazyValue('  spaces  ');
        $context   = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('  spaces  ', $result);
    }

    public function test_resolve_string_with_unicode_characters(): void
    {
        $lazyValue = new LazyValue('Hello 世界 🌍');
        $context   = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('Hello 世界 🌍', $result);
    }

    public function test_closure_return_type_is_respected(): void
    {
        $lazyValue = new LazyValue(function (ActionContext $context): string {
            return '123';
        });
        $context = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertIsString($result);
        $this->assertSame('123', $result);
    }

    public function test_resolve_with_long_string(): void
    {
        $longString = str_repeat('Long string content. ', 100);
        $lazyValue  = new LazyValue($longString);
        $context    = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame($longString, $result);
    }

    public function test_resolve_closure_with_complex_logic(): void
    {
        $lazyValue = new LazyValue(function (ActionContext $context): string {
            $parts    = ['Part', 'One'];
            $combined = implode(' ', $parts);

            return strtolower($combined);
        });
        $context = $this->createActionContext();

        $result = $lazyValue->resolve($context);

        $this->assertSame('part one', $result);
    }

    public function test_resolve_closure_can_use_context_instance(): void
    {
        $context = $this->createActionContext();

        $lazyValue = new LazyValue(function (ActionContext $ctx): string {
            // Using instanceof to verify we got the right type
            return $ctx instanceof ActionContext ? 'Valid Context' : 'Invalid';
        });

        $result = $lazyValue->resolve($context);

        $this->assertSame('Valid Context', $result);
    }

    private function createActionContext(): ActionContext
    {
        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        return new ActionContext($request, $config);
    }
}
