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
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Capabilities\Confirmation;
use BrickNPC\EloquentTables\Actions\Contributions\ConfirmationContribution;

/**
 * @internal
 */
#[CoversClass(Confirmation::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(CapabilityContribution::class)]
#[UsesClass(ConfirmationContribution::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(Config::class)]
class ConfirmationTest extends TestCase
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

    public function test_confirmation_is_instance_of_action_capability(): void
    {
        $confirmation = new Confirmation('Are you sure?');

        $this->assertInstanceOf(ActionCapability::class, $confirmation);
    }

    public function test_confirmation_is_final_class(): void
    {
        $reflection = new \ReflectionClass(Confirmation::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_contribute_returns_capability_contribution(): void
    {
        $confirmation = new Confirmation('Are you sure?');

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(CapabilityContribution::class, $result);
    }

    public function test_contribute_returns_confirmation_contribution(): void
    {
        $confirmation = new Confirmation('Are you sure?');

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_string_text_only(): void
    {
        $confirmation = new Confirmation('Delete this item?');

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_all_string_parameters(): void
    {
        $confirmation = new Confirmation(
            text: 'Delete this item?',
            confirmValue: 'Yes, delete',
            cancelValue: 'No, cancel',
            inputConfirmationValue: 'DELETE',
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_closure_text(): void
    {
        $confirmation = new Confirmation(
            fn (ActionContext $context): string => 'Dynamic confirmation message',
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_all_closure_parameters(): void
    {
        $confirmation = new Confirmation(
            text: fn (ActionContext $context): string => 'Delete this?',
            confirmValue: fn (ActionContext $context): string => 'Confirm',
            cancelValue: fn (ActionContext $context): string => 'Cancel',
            inputConfirmationValue: fn (ActionContext $context): string => 'DELETE',
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_mixed_string_and_closure_parameters(): void
    {
        $confirmation = new Confirmation(
            text: 'Delete this item?',
            confirmValue: fn (ActionContext $context): string => 'Yes',
            cancelValue: 'No',
            inputConfirmationValue: fn (ActionContext $context): string => 'DELETE',
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_null_optional_parameters(): void
    {
        $confirmation = new Confirmation(
            text: 'Are you sure?',
            confirmValue: null,
            cancelValue: null,
            inputConfirmationValue: null,
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_closure_receives_context(): void
    {
        $contextPassed = null;

        $confirmation = new Confirmation(
            text: function (ActionContext $ctx) use (&$contextPassed): string {
                $contextPassed = $ctx;

                return 'Confirm?';
            },
        );

        $confirmation->contribute($this->descriptor, $this->context);

        $this->assertSame($this->context, $contextPassed);
    }

    public function test_contribute_all_closures_receive_context(): void
    {
        $contextsPassed = [];

        $confirmation = new Confirmation(
            text: function (ActionContext $ctx) use (&$contextsPassed): string {
                $contextsPassed['text'] = $ctx;

                return 'Text';
            },
            confirmValue: function (ActionContext $ctx) use (&$contextsPassed): string {
                $contextsPassed['confirm'] = $ctx;

                return 'Confirm';
            },
            cancelValue: function (ActionContext $ctx) use (&$contextsPassed): string {
                $contextsPassed['cancel'] = $ctx;

                return 'Cancel';
            },
            inputConfirmationValue: function (ActionContext $ctx) use (&$contextsPassed): string {
                $contextsPassed['input'] = $ctx;

                return 'DELETE';
            },
        );

        $confirmation->contribute($this->descriptor, $this->context);

        $this->assertCount(4, $contextsPassed);
        $this->assertSame($this->context, $contextsPassed['text']);
        $this->assertSame($this->context, $contextsPassed['confirm']);
        $this->assertSame($this->context, $contextsPassed['cancel']);
        $this->assertSame($this->context, $contextsPassed['input']);
    }

    public function test_contribute_can_be_called_multiple_times(): void
    {
        $confirmation = new Confirmation('Are you sure?');

        $result1 = $confirmation->contribute($this->descriptor, $this->context);
        $result2 = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result1);
        $this->assertInstanceOf(ConfirmationContribution::class, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function test_contribute_with_empty_string_text(): void
    {
        $confirmation = new Confirmation('');

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_closure_can_use_external_variables(): void
    {
        $itemName = 'User Account';

        $confirmation = new Confirmation(
            text: fn (ActionContext $context): string => "Delete {$itemName}?",
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_multiline_text(): void
    {
        $confirmation = new Confirmation("Are you sure?\nThis action cannot be undone.");

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_special_characters_in_text(): void
    {
        $confirmation = new Confirmation(
            text: 'Delete "Item #123" & confirm?',
            confirmValue: "Yes, I'm sure",
            cancelValue: "No, don't delete",
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_unicode_characters(): void
    {
        $confirmation = new Confirmation(
            text: '¿Estás seguro? 你确定吗？',
            confirmValue: 'Sí',
            cancelValue: 'No',
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_with_different_contexts(): void
    {
        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $context1 = new ActionContext($request, $config);
        $context2 = new ActionContext($request, $config);

        $confirmation = new Confirmation('Confirm action?');

        $result1 = $confirmation->contribute($this->descriptor, $context1);
        $result2 = $confirmation->contribute($this->descriptor, $context2);

        $this->assertInstanceOf(ConfirmationContribution::class, $result1);
        $this->assertInstanceOf(ConfirmationContribution::class, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function test_contribute_with_different_descriptors(): void
    {
        $descriptor1 = new ActionDescriptor(/* add required parameters */);
        $descriptor2 = new ActionDescriptor(/* add required parameters */);

        $confirmation = new Confirmation('Confirm action?');

        $result1 = $confirmation->contribute($descriptor1, $this->context);
        $result2 = $confirmation->contribute($descriptor2, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result1);
        $this->assertInstanceOf(ConfirmationContribution::class, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function test_multiple_confirmation_instances_are_independent(): void
    {
        $confirmation1 = new Confirmation('Delete?');
        $confirmation2 = new Confirmation('Archive?');

        $result1 = $confirmation1->contribute($this->descriptor, $this->context);
        $result2 = $confirmation2->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result1);
        $this->assertInstanceOf(ConfirmationContribution::class, $result2);
        $this->assertNotSame($result1, $result2);
    }

    public function test_contribute_closure_with_complex_logic(): void
    {
        $confirmation = new Confirmation(
            text: function (ActionContext $context): string {
                $parts = ['Delete', 'this', 'item'];

                return implode(' ', $parts) . '?';
            },
        );

        $result = $confirmation->contribute($this->descriptor, $this->context);

        $this->assertInstanceOf(ConfirmationContribution::class, $result);
    }

    public function test_contribute_closures_are_evaluated_each_time(): void
    {
        $counter = 0;

        $confirmation = new Confirmation(
            text: function (ActionContext $context) use (&$counter): string {
                ++$counter;

                return "Confirmation #{$counter}";
            },
        );

        $confirmation->contribute($this->descriptor, $this->context);
        $confirmation->contribute($this->descriptor, $this->context);

        $this->assertSame(2, $counter);
    }
}
