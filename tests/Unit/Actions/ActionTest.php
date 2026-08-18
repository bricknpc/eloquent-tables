<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions;

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;
use BrickNPC\EloquentTables\Exceptions\ActionIntentAlreadySet;

/**
 * @internal
 */
#[CoversClass(Action::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(ActionIntentAlreadySet::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(CapabilityContribution::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(StyleSet::class)]
class ActionTest extends TestCase
{
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
    }

    public function test_style_returns_the_action_for_chaining(): void
    {
        $action = new Action();

        $this->assertSame($action, $action->style(ButtonStyle::Danger));
    }

    public function test_style_puts_a_style_set_on_the_descriptor(): void
    {
        $action = new Action()->style(ButtonStyle::Danger);

        $this->assertSame([ButtonStyle::Danger], $action->descriptor($this->context)?->style?->resolve($this->context));
    }

    public function test_an_action_without_a_style_has_no_style_set(): void
    {
        $this->assertNull(new Action()->descriptor($this->context)?->style);
    }

    public function test_a_second_style_call_merges_rather_than_replaces(): void
    {
        $action = new Action()
            ->style(ButtonStyle::Danger)
            ->style(ButtonStyle::Link);

        $this->assertSame(
            [ButtonStyle::Danger, ButtonStyle::Link],
            $action->descriptor($this->context)?->style?->resolve($this->context),
        );
    }

    public function test_style_accepts_a_closure_alongside_the_static_cases(): void
    {
        $action = new Action()->style(ButtonStyle::Danger, static fn() => ButtonStyle::Link);

        $this->assertSame(
            [ButtonStyle::Danger, ButtonStyle::Link],
            $action->descriptor($this->context)?->style?->resolve($this->context),
        );
    }

    public function test_a_closure_may_be_declared_before_the_static_cases(): void
    {
        $action = new Action()
            ->style(static fn() => ButtonStyle::Link)
            ->style(ButtonStyle::Danger);

        $this->assertSame(
            [ButtonStyle::Danger, ButtonStyle::Link],
            $action->descriptor($this->context)?->style?->resolve($this->context),
        );
    }

    public function test_the_style_set_survives_a_descriptor_call(): void
    {
        $action = new Action()->style(ButtonStyle::Danger);

        $action->descriptor($this->context);

        $this->assertSame([ButtonStyle::Danger], $action->descriptor($this->context)?->style?->resolve($this->context));
    }

    public function test_the_style_set_resolves_fresh_for_every_context(): void
    {
        $action = new Action()->style(static fn(ActionContext $context) => $context->model === null
            ? ButtonStyle::Link
            : ButtonStyle::Danger);

        $withoutModel = new ActionContext($this->context->request, $this->context->config);
        $withModel    = new ActionContext($this->context->request, $this->context->config, new TestModel());

        $this->assertSame([ButtonStyle::Link], $action->descriptor($withoutModel)?->style?->resolve($withoutModel));
        $this->assertSame([ButtonStyle::Danger], $action->descriptor($withModel)?->style?->resolve($withModel));
    }

    public function test_label_returns_the_action_for_chaining(): void
    {
        $action = new Action();

        $this->assertSame($action, $action->label('Edit'));
    }

    public function test_as_returns_the_action_for_chaining(): void
    {
        $action = new Action();

        $this->assertSame($action, $action->as($this->createIntent()));
    }

    public function test_with_returns_the_action_for_chaining(): void
    {
        $action = new Action();

        $this->assertSame($action, $action->with(new ActionCapability()));
    }

    public function test_the_label_defaults_to_null(): void
    {
        $descriptor = new Action()->descriptor($this->context);

        $this->assertNull($descriptor?->label->resolve($this->context));
    }

    public function test_a_string_label_ends_up_on_the_descriptor(): void
    {
        $descriptor = new Action()
            ->label('Edit')
            ->descriptor($this->context);

        $this->assertSame('Edit', $descriptor?->label->resolve($this->context));
    }

    public function test_a_closure_label_is_resolved_with_the_context(): void
    {
        $action = new Action()->label(static fn(ActionContext $context) => $context->isBulk
            ? 'Delete selected'
            : 'Delete');

        $this->assertSame('Delete', $action->descriptor($this->context)?->label->resolve($this->context));
        $this->assertSame(
            'Delete selected',
            $action->descriptor($this->context)?->label->resolve($this->context->isBulk()),
        );
    }

    public function test_the_last_label_wins(): void
    {
        $descriptor = new Action()
            ->label('Edit')
            ->label('Update')
            ->descriptor($this->context);

        $this->assertSame('Update', $descriptor?->label->resolve($this->context));
    }

    public function test_the_intent_ends_up_on_the_descriptor(): void
    {
        $intent = $this->createIntent();

        $descriptor = new Action()->as($intent)->descriptor($this->context);

        $this->assertSame($intent, $descriptor?->intent);
    }

    public function test_the_intent_defaults_to_null(): void
    {
        $descriptor = new Action()->descriptor($this->context);

        $this->assertNull($descriptor?->intent);
    }

    public function test_setting_a_second_intent_throws(): void
    {
        $action = new Action()->as($this->createIntent());

        $this->expectException(ActionIntentAlreadySet::class);

        $action->as($this->createIntent());
    }

    public function test_the_already_set_exception_carries_both_intents_and_the_action(): void
    {
        $first  = $this->createIntent();
        $second = $this->createIntent();

        $action = new Action()->as($first);

        try {
            $action->as($second);
        } catch (ActionIntentAlreadySet $exception) {
            $this->assertSame($first, $exception->context()['intent']);
            $this->assertSame($second, $exception->context()['newIntent']);
            $this->assertSame($action, $exception->context()['action']);

            return;
        }

        $this->fail('The action did not throw an ActionIntentAlreadySet exception.');
    }

    public function test_the_first_intent_is_kept_when_a_second_one_is_refused(): void
    {
        $intent = $this->createIntent();

        $action = new Action()->as($intent);

        try {
            $action->as($this->createIntent());

            // @mago-expect lint:no-empty-catch-clause -- swallowing it is the point: the test asserts what survives
            // the failed second call, not the throw itself, which its own test already covers
        } catch (ActionIntentAlreadySet) {
        }

        $this->assertSame($intent, $action->descriptor($this->context)?->intent);
    }

    public function test_has_descriptor_returns_true_without_capabilities(): void
    {
        $this->assertTrue(new Action()->hasDescriptor($this->context));
    }

    public function test_has_descriptor_returns_true_when_every_capability_passes(): void
    {
        $action = new Action()
            ->with($this->createCapability(check: true))
            ->with($this->createCapability(check: true));

        $this->assertTrue($action->hasDescriptor($this->context));
    }

    public function test_has_descriptor_returns_false_when_one_capability_fails(): void
    {
        $action = new Action()
            ->with($this->createCapability(check: true))
            ->with($this->createCapability(check: false));

        $this->assertFalse($action->hasDescriptor($this->context));
    }

    public function test_has_descriptor_is_evaluated_per_context(): void
    {
        $action = new Action()->with($this->createCapability(check: static fn(
            ActionDescriptor $descriptor,
            ActionContext $context,
        ) => $context->isBulk));

        $this->assertFalse($action->hasDescriptor($this->context));
        $this->assertTrue($action->hasDescriptor($this->context->isBulk()));
    }

    public function test_descriptor_returns_null_when_a_capability_fails(): void
    {
        $action = new Action()
            ->label('Edit')
            ->with($this->createCapability(check: false));

        $this->assertNull($action->descriptor($this->context));
    }

    public function test_descriptor_returns_a_descriptor_when_every_capability_passes(): void
    {
        $action = new Action()->with($this->createCapability(check: true));

        $this->assertInstanceOf(ActionDescriptor::class, $action->descriptor($this->context));
    }

    public function test_descriptor_returns_the_same_descriptor_on_every_call(): void
    {
        $action = new Action()->label('Edit');

        $this->assertSame($action->descriptor($this->context), $action->descriptor($this->context));
    }

    public function test_descriptor_applies_every_capability(): void
    {
        $action = new Action()
            ->with($this->createCapability(apply: static function (ActionDescriptor $descriptor): void {
                $descriptor->attributes['class'] = 'btn-danger';
            }))
            ->with($this->createCapability(apply: static function (ActionDescriptor $descriptor): void {
                $descriptor->attributes['title'] = 'Delete';
            }));

        $descriptor = $action->descriptor($this->context);

        $this->assertSame(['class' => 'btn-danger', 'title' => 'Delete'], $descriptor?->attributes);
    }

    public function test_descriptor_applies_the_capabilities_in_registration_order(): void
    {
        $applied = [];

        $action = new Action()
            ->with($this->createCapability(apply: static function () use (&$applied): void {
                $applied[] = 'first';
            }))
            ->with($this->createCapability(apply: static function () use (&$applied): void {
                $applied[] = 'second';
            }));

        $action->descriptor($this->context);

        $this->assertSame(['first', 'second'], $applied);
    }

    public function test_descriptor_does_not_apply_the_capabilities_when_one_fails(): void
    {
        $applied = false;

        $action = new Action()
            ->with($this->createCapability(apply: static function () use (&$applied): void {
                $applied = true;
            }))
            ->with($this->createCapability(check: false));

        $action->descriptor($this->context);

        $this->assertFalse($applied);
    }

    public function test_descriptor_collects_the_contributions_in_the_render_buffers(): void
    {
        $action = new Action()->with($this->createCapability(contribution: $this->createContribution(
            'before',
            'data-confirm="true"',
            'after',
        )));

        $descriptor = $action->descriptor($this->context);

        $this->assertSame('before', $descriptor?->beforeRender->render());
        $this->assertSame('data-confirm="true"', $descriptor?->attributesRender->render());
        $this->assertSame('after', $descriptor?->afterRender->render());
    }

    public function test_descriptor_collects_the_contributions_of_every_capability(): void
    {
        $action = new Action()
            ->with($this->createCapability(contribution: $this->createContribution(attributes: 'data-one="1"')))
            ->with($this->createCapability(contribution: $this->createContribution(attributes: 'data-two="2"')));

        $descriptor = $action->descriptor($this->context);

        $this->assertSame('data-one="1"data-two="2"', $descriptor?->attributesRender->render());
    }

    public function test_descriptor_ignores_capabilities_without_a_contribution(): void
    {
        $action = new Action()
            ->with($this->createCapability())
            ->with($this->createCapability(contribution: $this->createContribution(attributes: 'data-one="1"')));

        $descriptor = $action->descriptor($this->context);

        $this->assertSame('data-one="1"', $descriptor?->attributesRender->render());
    }

    public function test_descriptor_empties_the_buffers_between_calls(): void
    {
        $action = new Action()->with($this->createCapability(contribution: $this->createContribution(
            'before',
            'data-confirm="true"',
            'after',
        )));

        $action->descriptor($this->context);
        $descriptor = $action->descriptor($this->context);

        $this->assertSame('before', $descriptor?->beforeRender->render());
        $this->assertSame('data-confirm="true"', $descriptor?->attributesRender->render());
        $this->assertSame('after', $descriptor?->afterRender->render());
    }

    public function test_descriptor_passes_the_context_to_the_capabilities(): void
    {
        $contexts = [];

        $capability = $this->createCapability(check: static function (
            ActionDescriptor $descriptor,
            ActionContext $context,
        ) use (&$contexts): bool {
            $contexts[] = $context;

            return true;
        }, apply: static function (ActionDescriptor $descriptor, ActionContext $context) use (&$contexts): void {
            $contexts[] = $context;
        });

        new Action()
            ->with($capability)
            ->descriptor($this->context);

        $this->assertCount(2, $contexts);
        $this->assertSame($this->context, $contexts[0]);
        $this->assertSame($this->context, $contexts[1]);
    }

    private function createIntent(): ActionIntent
    {
        return new class extends ActionIntent {
            public function view(): string
            {
                return 'eloquent-tables::actions.http';
            }
        };
    }

    private function createCapability(
        bool|\Closure $check = true,
        ?\Closure $apply = null,
        ?CapabilityContribution $contribution = null,
    ): ActionCapability {
        return new class($check, $apply, $contribution) extends ActionCapability {
            public function __construct(
                private readonly bool|\Closure $checkValue,
                private readonly ?\Closure $applyCallback,
                private readonly ?CapabilityContribution $contribution,
            ) {}

            public function check(ActionDescriptor $descriptor, ActionContext $context): bool
            {
                if ($this->checkValue instanceof \Closure) {
                    return (bool) call_user_func($this->checkValue, $descriptor, $context);
                }

                return $this->checkValue;
            }

            public function apply(ActionDescriptor $descriptor, ActionContext $context): void
            {
                if ($this->applyCallback !== null) {
                    call_user_func($this->applyCallback, $descriptor, $context);
                }
            }

            public function contribute(ActionDescriptor $descriptor, ActionContext $context): ?CapabilityContribution
            {
                return $this->contribution;
            }
        };
    }

    private function createContribution(
        ?string $before = null,
        ?string $attributes = null,
        ?string $after = null,
    ): CapabilityContribution {
        return new class($before, $attributes, $after) extends CapabilityContribution {
            public function __construct(
                private readonly ?string $before,
                private readonly ?string $attributes,
                private readonly ?string $after,
            ) {}

            public function renderBefore(ActionDescriptor $descriptor, ActionContext $context): ?string
            {
                return $this->before;
            }

            public function renderAttributes(ActionDescriptor $descriptor, ActionContext $context): ?string
            {
                return $this->attributes;
            }

            public function renderAfter(ActionDescriptor $descriptor, ActionContext $context): ?string
            {
                return $this->after;
            }
        };
    }
}
