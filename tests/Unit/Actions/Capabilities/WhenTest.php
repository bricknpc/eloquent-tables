<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Capabilities;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Capabilities\When;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(When::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
class WhenTest extends TestCase
{
    private ActionDescriptor $descriptor;
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->descriptor = new ActionDescriptor();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
    }

    public function test_when_is_instance_of_action_capability(): void
    {
        $when = new When(fn (ActionContext $context): bool => true);

        $this->assertInstanceOf(ActionCapability::class, $when);
    }

    public function test_when_is_final_class(): void
    {
        $reflection = new \ReflectionClass(When::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_check_returns_true_when_closure_returns_true(): void
    {
        $when = new When(fn (ActionContext $context): bool => true);

        $this->assertTrue($when->check($this->descriptor, $this->context));
    }

    public function test_check_returns_false_when_closure_returns_false(): void
    {
        $when = new When(fn (ActionContext $context): bool => false);

        $this->assertFalse($when->check($this->descriptor, $this->context));
    }

    public function test_check_passes_context_to_closure(): void
    {
        $contextPassed = null;

        $when = new When(function (ActionContext $ctx) use (&$contextPassed): bool {
            $contextPassed = $ctx;

            return true;
        });

        $when->check($this->descriptor, $this->context);

        $this->assertSame($this->context, $contextPassed);
    }

    public function test_check_can_use_the_model_from_the_context(): void
    {
        $when = new When(fn (ActionContext $context): bool => $context->model !== null);

        $this->assertFalse($when->check($this->descriptor, $this->context));
    }

    public function test_check_can_use_the_bulk_flag_from_the_context(): void
    {
        $when = new When(fn (ActionContext $context): bool => $context->isBulk);

        $this->assertFalse($when->check($this->descriptor, $this->context));
        $this->assertTrue($when->check($this->descriptor, $this->context->isBulk()));
    }

    public function test_check_casts_truthy_return_value_to_true(): void
    {
        $when = new When(fn (ActionContext $context) => 1);

        $this->assertTrue($when->check($this->descriptor, $this->context));
    }

    public function test_check_casts_falsy_return_value_to_false(): void
    {
        $when = new When(fn (ActionContext $context) => 0);

        $this->assertFalse($when->check($this->descriptor, $this->context));
    }

    public function test_check_casts_non_empty_string_to_true(): void
    {
        $when = new When(fn (ActionContext $context) => 'yes');

        $this->assertTrue($when->check($this->descriptor, $this->context));
    }

    public function test_check_casts_empty_string_to_false(): void
    {
        $when = new When(fn (ActionContext $context) => '');

        $this->assertFalse($when->check($this->descriptor, $this->context));
    }

    public function test_check_is_evaluated_on_every_call(): void
    {
        $callCount = 0;

        $when = new When(function (ActionContext $context) use (&$callCount): bool {
            ++$callCount;

            return true;
        });

        $when->check($this->descriptor, $this->context);
        $when->check($this->descriptor, $this->context);

        $this->assertSame(2, $callCount);
    }

    public function test_check_can_return_a_different_result_per_call(): void
    {
        $allowed = false;

        $when = new When(function (ActionContext $context) use (&$allowed): bool {
            return $allowed;
        });

        $this->assertFalse($when->check($this->descriptor, $this->context));

        $allowed = true;

        $this->assertTrue($when->check($this->descriptor, $this->context));
    }

    public function test_contribute_returns_null(): void
    {
        $when = new When(fn (ActionContext $context): bool => true);

        $this->assertNull($when->contribute($this->descriptor, $this->context));
    }

    public function test_apply_does_not_change_the_descriptor(): void
    {
        $when = new When(fn (ActionContext $context): bool => true);

        $when->apply($this->descriptor, $this->context);

        $this->assertSame([], $this->descriptor->attributes);
        $this->assertNull($this->descriptor->intent);
        $this->assertSame('', $this->descriptor->beforeRender->render());
        $this->assertSame('', $this->descriptor->attributesRender->render());
        $this->assertSame('', $this->descriptor->afterRender->render());
    }
}
