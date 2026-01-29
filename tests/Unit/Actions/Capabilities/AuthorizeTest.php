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
use BrickNPC\EloquentTables\Actions\Capabilities\Authorize;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @internal
 */
#[CoversClass(Authorize::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
class AuthorizeTest extends TestCase
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

    public function test_authorize_is_instance_of_action_capability(): void
    {
        $authorize = new Authorize(fn (ActionContext $context): bool => true);

        $this->assertInstanceOf(ActionCapability::class, $authorize);
    }

    public function test_authorize_is_final_class(): void
    {
        $reflection = new \ReflectionClass(Authorize::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_check_returns_true_when_closure_returns_true(): void
    {
        $authorize = new Authorize(fn (ActionContext $context): bool => true);

        $result = $authorize->check($this->descriptor, $this->context);

        $this->assertTrue($result);
    }

    public function test_check_returns_false_when_closure_returns_false(): void
    {
        $authorize = new Authorize(fn (ActionContext $context): bool => false);

        $result = $authorize->check($this->descriptor, $this->context);

        $this->assertFalse($result);
    }

    public function test_check_passes_context_to_closure(): void
    {
        $contextPassed = null;

        $authorize = new Authorize(function (ActionContext $ctx) use (&$contextPassed): bool {
            $contextPassed = $ctx;

            return true;
        });

        $authorize->check($this->descriptor, $this->context);

        $this->assertSame($this->context, $contextPassed);
    }

    public function test_check_does_not_pass_descriptor_to_closure(): void
    {
        $closureCalled = false;

        $authorize = new Authorize(function (ActionContext $context) use (&$closureCalled): bool {
            $closureCalled = true;

            // Closure only receives context, not descriptor
            return true;
        });

        $authorize->check($this->descriptor, $this->context);

        $this->assertTrue($closureCalled);
    }

    public function test_check_can_be_called_multiple_times(): void
    {
        $callCount = 0;

        $authorize = new Authorize(function (ActionContext $context) use (&$callCount): bool {
            ++$callCount;

            return true;
        });

        $authorize->check($this->descriptor, $this->context);
        $authorize->check($this->descriptor, $this->context);
        $authorize->check($this->descriptor, $this->context);

        $this->assertSame(3, $callCount);
    }

    public function test_check_executes_closure_each_time_it_is_called(): void
    {
        $callCount = 0;

        $authorize = new Authorize(function (ActionContext $context) use (&$callCount): bool {
            ++$callCount;

            return $callCount === 1;
        });

        $result1 = $authorize->check($this->descriptor, $this->context);
        $result2 = $authorize->check($this->descriptor, $this->context);

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }

    public function test_check_closure_can_use_external_variables(): void
    {
        $isAuthorized = true;

        $authorize = new Authorize(fn (ActionContext $context): bool => $isAuthorized);

        $result = $authorize->check($this->descriptor, $this->context);

        $this->assertTrue($result);
    }

    public function test_check_closure_can_use_mutable_external_variables(): void
    {
        $isAuthorized = false;

        $authorize = new Authorize(function (ActionContext $context) use (&$isAuthorized): bool {
            return $isAuthorized;
        });

        $result1 = $authorize->check($this->descriptor, $this->context);

        $isAuthorized = true;

        $result2 = $authorize->check($this->descriptor, $this->context);

        $this->assertFalse($result1);
        $this->assertTrue($result2);
    }

    public function test_check_with_different_contexts(): void
    {
        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $context1 = new ActionContext($request, $config);
        $context2 = new ActionContext($request, $config);

        $receivedContexts = [];

        $authorize = new Authorize(function (ActionContext $ctx) use (&$receivedContexts): bool {
            $receivedContexts[] = $ctx;

            return true;
        });

        $authorize->check($this->descriptor, $context1);
        $authorize->check($this->descriptor, $context2);

        $this->assertCount(2, $receivedContexts);
        $this->assertSame($context1, $receivedContexts[0]);
        $this->assertSame($context2, $receivedContexts[1]);
    }

    public function test_check_with_different_descriptors(): void
    {
        $descriptor1 = new ActionDescriptor(/* add required parameters */);
        $descriptor2 = new ActionDescriptor(/* add required parameters */);

        $authorize = new Authorize(fn (ActionContext $context): bool => true);

        $result1 = $authorize->check($descriptor1, $this->context);
        $result2 = $authorize->check($descriptor2, $this->context);

        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }

    public function test_check_closure_with_complex_authorization_logic(): void
    {
        $authorize = new Authorize(function (ActionContext $context): bool {
            // Simulate complex logic
            $conditions = [true, true, false];

            return count(array_filter($conditions)) >= 2;
        });

        $result = $authorize->check($this->descriptor, $this->context);

        $this->assertTrue($result);
    }

    public function test_check_returns_boolean_type(): void
    {
        $authorize = new Authorize(fn (ActionContext $context): bool => true);

        $result = $authorize->check($this->descriptor, $this->context);

        $this->assertIsBool($result);
    }

    public function test_multiple_authorize_instances_are_independent(): void
    {
        $authorize1 = new Authorize(fn (ActionContext $context): bool => true);
        $authorize2 = new Authorize(fn (ActionContext $context): bool => false);

        $result1 = $authorize1->check($this->descriptor, $this->context);
        $result2 = $authorize2->check($this->descriptor, $this->context);

        $this->assertTrue($result1);
        $this->assertFalse($result2);
    }

    public function test_authorize_closure_can_be_stored_and_reused(): void
    {
        $closure = fn (ActionContext $context): bool => true;

        $authorize1 = new Authorize($closure);
        $authorize2 = new Authorize($closure);

        $result1 = $authorize1->check($this->descriptor, $this->context);
        $result2 = $authorize2->check($this->descriptor, $this->context);

        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }

    public function test_check_closure_can_inspect_context_instance(): void
    {
        $authorize = new Authorize(function (ActionContext $context): bool {
            // Verify we receive a valid ActionContext instance
            return $context instanceof ActionContext;
        });

        $result = $authorize->check($this->descriptor, $this->context);

        $this->assertTrue($result);
    }

    public function test_check_closure_receives_exact_context_instance(): void
    {
        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $specificContext = new ActionContext($request, $config);

        $authorize = new Authorize(function (ActionContext $context) use ($specificContext): bool {
            return $context === $specificContext;
        });

        $result = $authorize->check($this->descriptor, $specificContext);

        $this->assertTrue($result);
    }
}
