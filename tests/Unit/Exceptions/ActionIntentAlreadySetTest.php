<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use BrickNPC\EloquentTables\Actions\Action;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Exceptions\ActionIntentAlreadySet;

/**
 * @internal
 */
#[CoversClass(ActionIntentAlreadySet::class)]
#[UsesClass(Action::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(LazyValue::class)]
class ActionIntentAlreadySetTest extends TestCase
{
    public function test_for_intent_creates_exception_with_correct_message(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);

        $expectedMessage = sprintf(
            'The action %s already has an intent %s, new intent %s can not be set',
            get_class($action),
            get_class($intent),
            get_class($newIntent),
        );

        $this->assertSame($expectedMessage, $exception->getMessage());
    }

    public function test_for_intent_returns_instance_of_action_intent_already_set(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);

        $this->assertInstanceOf(ActionIntentAlreadySet::class, $exception);
    }

    public function test_for_intent_returns_instance_of_exception(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);

        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_context_returns_all_objects(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);
        $context   = $exception->context();

        $this->assertIsArray($context);
        $this->assertCount(3, $context);
        $this->assertArrayHasKey('intent', $context);
        $this->assertArrayHasKey('newIntent', $context);
        $this->assertArrayHasKey('action', $context);
    }

    public function test_context_returns_same_intent_object(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);
        $context   = $exception->context();

        $this->assertSame($intent, $context['intent']);
    }

    public function test_context_returns_same_new_intent_object(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);
        $context   = $exception->context();

        $this->assertSame($newIntent, $context['newIntent']);
    }

    public function test_context_returns_same_action_object(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);
        $context   = $exception->context();

        $this->assertSame($action, $context['action']);
    }

    public function test_context_can_be_called_multiple_times(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);

        $context1 = $exception->context();
        $context2 = $exception->context();

        $this->assertEquals($context1, $context2);
        $this->assertSame($context1['intent'], $context2['intent']);
        $this->assertSame($context1['newIntent'], $context2['newIntent']);
        $this->assertSame($context1['action'], $context2['action']);
    }

    public function test_exception_can_be_thrown_and_caught(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $this->expectException(ActionIntentAlreadySet::class);

        throw ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);
    }

    public function test_exception_message_includes_concrete_class_names(): void
    {
        $intent = new class extends ActionIntent {
            public function view(): string
            {
                return '';
            }
        };
        $newIntent = new class extends ActionIntent {
            public function view(): string
            {
                return '';
            }
        };
        $action = new class extends Action {
            public function handle(): void {}
        };

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);
        $message   = $exception->getMessage();

        $this->assertStringContainsString('already has an intent', $message);
        $this->assertStringContainsString('new intent', $message);
        $this->assertStringContainsString('can not be set', $message);
    }

    public function test_exception_preserves_object_identity_through_context(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);

        try {
            throw $exception;
        } catch (ActionIntentAlreadySet $caught) {
            $context = $caught->context();

            $this->assertSame($intent, $context['intent']);
            $this->assertSame($newIntent, $context['newIntent']);
            $this->assertSame($action, $context['action']);
        }
    }

    public function test_exception_has_default_code_zero(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);

        $this->assertSame(0, $exception->getCode());
    }

    public function test_context_returns_correct_types(): void
    {
        $intent    = $this->createMock(ActionIntent::class);
        $newIntent = $this->createMock(ActionIntent::class);
        $action    = $this->createMock(Action::class);

        $exception = ActionIntentAlreadySet::forIntent($intent, $newIntent, $action);
        $context   = $exception->context();

        $this->assertInstanceOf(ActionIntent::class, $context['intent']);
        $this->assertInstanceOf(ActionIntent::class, $context['newIntent']);
        $this->assertInstanceOf(Action::class, $context['action']);
    }
}
