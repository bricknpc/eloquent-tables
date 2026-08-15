<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use BrickNPC\EloquentTables\Actions\Action;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Exceptions\ActionIntentNotSet;

/**
 * @internal
 */
#[CoversClass(ActionIntentNotSet::class)]
#[UsesClass(Action::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(LazyValue::class)]
class ActionIntentNotSetTest extends TestCase
{
    public function test_for_action_creates_exception_with_correct_message(): void
    {
        $action = $this->createMock(Action::class);

        $exception = ActionIntentNotSet::forAction($action);

        $expectedMessage = sprintf(
            'The action %s has no intent and can not be rendered, set one with the as() method',
            get_class($action),
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function test_for_action_returns_instance_of_action_intent_not_set(): void
    {
        $exception = ActionIntentNotSet::forAction($this->createMock(Action::class));

        $this->assertInstanceOf(ActionIntentNotSet::class, $exception);
    }

    public function test_for_action_returns_instance_of_exception(): void
    {
        $exception = ActionIntentNotSet::forAction($this->createMock(Action::class));

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_context_returns_the_action(): void
    {
        $action = $this->createMock(Action::class);

        $context = ActionIntentNotSet::forAction($action)->context();

        $this->assertIsArray($context);
        $this->assertCount(1, $context);
        $this->assertArrayHasKey('action', $context);
        $this->assertSame($action, $context['action']);
    }

    public function test_it_names_the_concrete_action_class(): void
    {
        $action = new Action();

        $exception = ActionIntentNotSet::forAction($action);

        $this->assertStringContainsString(Action::class, $exception->getMessage());
    }

    public function test_it_has_no_previous_exception_and_a_zero_code(): void
    {
        $exception = ActionIntentNotSet::forAction($this->createMock(Action::class));

        $this->assertNull($exception->getPrevious());
        $this->assertSame(0, $exception->getCode());
    }

    public function test_it_can_be_thrown_and_caught(): void
    {
        $this->expectException(ActionIntentNotSet::class);

        throw ActionIntentNotSet::forAction($this->createMock(Action::class));
    }
}
