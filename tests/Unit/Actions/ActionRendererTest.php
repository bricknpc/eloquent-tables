<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions;

use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Exceptions\ActionIntentNotSet;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Capabilities\Confirmation;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;
use BrickNPC\EloquentTables\Actions\Contributions\ConfirmationContribution;

/**
 * @internal
 */
#[CoversClass(ActionRenderer::class)]
#[UsesClass(ActionIntentNotSet::class)]
#[UsesClass(Confirmation::class)]
#[UsesClass(CapabilityContribution::class)]
#[UsesClass(ConfirmationContribution::class)]
#[UsesClass(Action::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(ActionCollection::class)]
#[UsesClass(ActionCollectionType::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Http::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(Theme::class)]
class ActionRendererTest extends TestCase
{
    private ActionRenderer $renderer;
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->renderer = new ActionRenderer($config);
        $this->context  = new ActionContext($request, $config);
    }

    public function test_it_renders_an_action_with_the_view_of_its_intent(): void
    {
        $action = new Action()->label('Edit')->as($this->createIntent());

        $view = $this->renderer->render($action, $this->context);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('eloquent-tables::actions.http', $view->name());
    }

    public function test_it_refuses_to_render_an_action_without_an_intent(): void
    {
        $this->expectException(ActionIntentNotSet::class);

        $this->renderer->render(new Action()->label('Edit'), $this->context);
    }

    public function test_the_missing_intent_exception_names_the_action_and_the_fix(): void
    {
        $action = new Action()->label('Edit');

        try {
            $this->renderer->render($action, $this->context);
        } catch (ActionIntentNotSet $exception) {
            $this->assertStringContainsString(get_class($action), $exception->getMessage());
            $this->assertStringContainsString('as() method', $exception->getMessage());
            $this->assertSame($action, $exception->context()['action']);

            return;
        }

        $this->fail('The renderer did not throw an ActionIntentNotSet exception.');
    }

    public function test_it_refuses_to_render_a_collection_holding_an_action_without_an_intent(): void
    {
        $collection = new ActionCollection([new Action()->label('Edit')]);

        try {
            $this->renderer->render($collection, $this->context)?->render();
        } catch (\Throwable $exception) {
            $this->assertStringContainsString('has no intent and can not be rendered', $exception->getMessage());

            $cause = $exception;

            while ($cause->getPrevious() !== null) {
                $cause = $cause->getPrevious();
            }

            $this->assertInstanceOf(ActionIntentNotSet::class, $cause);

            return;
        }

        $this->fail('The renderer did not throw an ActionIntentNotSet exception.');
    }

    public function test_it_does_not_render_an_action_that_is_not_allowed(): void
    {
        $action = new Action()->label('Edit')->with($this->createCapability(false));

        $this->assertNull($this->renderer->render($action, $this->context));
    }

    public function test_it_passes_the_rendering_data_to_the_action_view(): void
    {
        $intent = $this->createIntent();

        $action = new Action()->label('Edit')->as($intent);

        $data = $this->renderer->render($action, $this->context)?->getData();

        $this->assertSame(Theme::Bootstrap5, $data['theme']);
        $this->assertSame('et', $data['dataNamespace']);
        $this->assertSame($this->context, $data['context']);
        $this->assertSame('Edit', $data['label']);
        $this->assertSame($intent, $data['intent']);
        $this->assertSame([], $data['attributes']);
        $this->assertInstanceOf(RenderBuffer::class, $data['beforeContent']);
        $this->assertInstanceOf(RenderBuffer::class, $data['afterContent']);
        $this->assertInstanceOf(RenderBuffer::class, $data['renderedAttributes']);
    }

    public function test_it_resolves_a_closure_label_against_the_context(): void
    {
        $action = new Action()
            ->label(fn (ActionContext $context) => $context->isBulk ? 'Delete selected' : 'Delete')
            ->as($this->createIntent())
        ;

        $data = $this->renderer->render($action, $this->context->isBulk())?->getData();

        $this->assertSame('Delete selected', $data['label']);
    }

    public function test_it_passes_the_attributes_set_by_a_capability_to_the_view(): void
    {
        $action = new Action()
            ->label('Delete')
            ->as($this->createIntent())
            ->with($this->createCapability(apply: function (ActionDescriptor $descriptor): void {
                $descriptor->attributes['class'] = 'btn-danger';
            }))
        ;

        $data = $this->renderer->render($action, $this->context)?->getData();

        $this->assertSame(['class' => 'btn-danger'], $data['attributes']);
    }

    public function test_it_gives_every_rendered_action_a_unique_id(): void
    {
        $action = new Action()->label('Edit')->as($this->createIntent());

        $first  = $this->renderer->render($action, $this->context)?->getData()['id'];
        $second = $this->renderer->render($action, $this->context)?->getData()['id'];

        $this->assertNotSame($first, $second);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $first);
    }

    public function test_it_calls_the_before_and_after_render_hooks_of_the_intent(): void
    {
        $calls = [];

        $intent = $this->createIntent()
            ->before(function () use (&$calls): void {
                $calls[] = 'before';
            })
            ->after(function () use (&$calls): void {
                $calls[] = 'after';
            })
        ;

        $this->renderer->render(new Action()->label('Edit')->as($intent), $this->context);

        $this->assertSame(['before', 'after'], $calls);
    }

    public function test_the_before_hook_can_still_change_the_data_of_the_view(): void
    {
        $intent = $this->createIntent()->before(function (ActionDescriptor $descriptor): void {
            $descriptor->attributes['class'] = 'btn-danger';
        });

        $data = $this->renderer->render(new Action()->label('Edit')->as($intent), $this->context)?->getData();

        $this->assertSame(['class' => 'btn-danger'], $data['attributes']);
    }

    public function test_it_does_not_call_the_hooks_of_an_action_that_is_not_allowed(): void
    {
        $called = false;

        $intent = $this->createIntent()->before(function () use (&$called): void {
            $called = true;
        });

        $action = new Action()->label('Edit')->as($intent)->with($this->createCapability(false));

        $this->renderer->render($action, $this->context);

        $this->assertFalse($called);
    }

    public function test_it_renders_a_get_action_as_a_link(): void
    {
        $action = new Action()->label('Edit')->as(new Http('https://example.com/users/1/edit'));

        $rendered = $this->renderer->render($action, $this->context)?->render();

        $this->assertStringContainsString('<a href="https://example.com/users/1/edit"', $rendered);
        $this->assertStringContainsString('Edit', $rendered);
    }

    public function test_it_renders_a_delete_action_as_a_form(): void
    {
        $action = new Action()->label('Delete')->as(new Http('https://example.com/users/1', Method::Delete));

        $rendered = $this->renderer->render($action, $this->context)?->render();

        $this->assertStringContainsString('<button type="submit"', $rendered);
        $this->assertStringContainsString('action="https://example.com/users/1"', $rendered);
        $this->assertStringContainsString('name="_method" value="DELETE"', $rendered);
    }

    public function test_it_marks_a_bulk_form_action_so_the_selected_keys_are_collected(): void
    {
        $action = new Action()->label('Delete')->as(new Http('https://example.com/users', Method::Delete));

        $rendered = $this->renderer->render($action, $this->context->isBulk())?->render();

        $this->assertStringContainsString('data-et-bulk-action-form="true"', $rendered);
    }

    public function test_it_marks_a_bulk_form_action_without_a_confirmation(): void
    {
        $action = new Action()->label('Delete')->as(new Http('https://example.com/users', Method::Delete));

        $rendered = $this->renderer->render($action, $this->context->isBulk())?->render();

        $this->assertStringContainsString('data-et-bulk-action-form="true"', $rendered);
        $this->assertStringNotContainsString('data-et-confirm', $rendered);
    }

    public function test_it_marks_a_bulk_form_action_that_also_has_a_confirmation(): void
    {
        $action = new Action()
            ->label('Delete')
            ->as(new Http('https://example.com/users', Method::Delete))
            ->with(new Confirmation('Are you sure?'))
        ;

        $rendered = $this->renderer->render($action, $this->context->isBulk())?->render();

        $this->assertStringContainsString('data-et-bulk-action-form="true"', $rendered);
        $this->assertStringContainsString('data-et-confirm="true"', $rendered);
    }

    public function test_it_uses_the_configured_data_namespace_for_the_bulk_marker(): void
    {
        $this->app->make('config')->set('eloquent-tables.data-namespace', 'tables');

        $action = new Action()->label('Delete')->as(new Http('https://example.com/users', Method::Delete));

        $rendered = $this->renderer->render($action, $this->context->isBulk())?->render();

        $this->assertStringContainsString('data-tables-bulk-action-form="true"', $rendered);
    }

    public function test_it_does_not_mark_a_form_action_outside_a_bulk_context(): void
    {
        $action = new Action()->label('Delete')->as(new Http('https://example.com/users/1', Method::Delete));

        $rendered = $this->renderer->render($action, $this->context)?->render();

        $this->assertStringNotContainsString('bulk-action-form', $rendered);
    }

    public function test_it_does_not_mark_a_bulk_action_that_renders_a_link(): void
    {
        $action = new Action()->label('Export')->as(new Http('https://example.com/users/export'));

        $rendered = $this->renderer->render($action, $this->context->isBulk())?->render();

        $this->assertStringNotContainsString('bulk-action-form', $rendered);
    }

    public function test_it_renders_a_collection_with_the_view_of_its_type(): void
    {
        $collection = new ActionCollection([new Action()->label('Edit')->as($this->createIntent())]);

        $view = $this->renderer->render($collection, $this->context);

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('eloquent-tables::actions.collection.default', $view->name());
    }

    public function test_it_renders_a_grouped_collection_with_the_group_view(): void
    {
        $collection = new ActionCollection(
            [new Action()->label('Edit')->as($this->createIntent())],
            ActionCollectionType::Grouped,
        );

        $this->assertSame(
            'eloquent-tables::actions.collection.group',
            $this->renderer->render($collection, $this->context)?->name(),
        );
    }

    public function test_it_renders_a_dropdown_collection_with_the_dropdown_view(): void
    {
        $collection = new ActionCollection(
            [new Action()->label('Edit')->as($this->createIntent())],
            ActionCollectionType::Dropdown,
        );

        $this->assertSame(
            'eloquent-tables::actions.collection.dropdown',
            $this->renderer->render($collection, $this->context)?->name(),
        );
    }

    public function test_it_passes_the_rendering_data_to_the_collection_view(): void
    {
        $collection = new ActionCollection([new Action()->label('Edit')->as($this->createIntent())])
            ->label('User actions')
        ;

        $data = $this->renderer->render($collection, $this->context)?->getData();

        $this->assertSame($collection, $data['actions']);
        $this->assertSame($this->context, $data['context']);
        $this->assertSame(Theme::Bootstrap5, $data['theme']);
        $this->assertSame('et', $data['dataNamespace']);
        $this->assertSame('User actions', $data['label']);
        $this->assertSame($this->renderer, $data['actionRenderer']);
    }

    public function test_it_resolves_a_closure_collection_label(): void
    {
        $collection = new ActionCollection([new Action()->label('Edit')->as($this->createIntent())])
            ->label(fn (ActionContext $context) => $context->isBulk ? 'Bulk actions' : 'Row actions')
        ;

        $data = $this->renderer->render($collection, $this->context)?->getData();

        $this->assertSame('Row actions', $data['label']);
    }

    public function test_it_does_not_render_an_empty_collection(): void
    {
        $this->assertNull($this->renderer->render(new ActionCollection(), $this->context));
    }

    public function test_it_does_not_render_a_collection_without_renderable_actions(): void
    {
        $collection = new ActionCollection([
            new Action()->label('Edit')->with($this->createCapability(false)),
        ]);

        $this->assertNull($this->renderer->render($collection, $this->context));
    }

    public function test_it_renders_every_action_of_a_collection(): void
    {
        $collection = new ActionCollection([
            new Action()->label('Edit')->as(new Http('https://example.com/users/1/edit')),
            new Action()->label('Show')->as(new Http('https://example.com/users/1')),
        ]);

        $rendered = $this->renderer->render($collection, $this->context)?->render();

        $this->assertStringContainsString('https://example.com/users/1/edit', $rendered);
        $this->assertStringContainsString('https://example.com/users/1"', $rendered);
    }

    public function test_it_skips_actions_that_are_not_allowed_when_rendering_a_collection(): void
    {
        $collection = new ActionCollection([
            new Action()->label('Edit')->as(new Http('https://example.com/users/1/edit')),
            new Action()->label('Delete')
                ->as(new Http('https://example.com/users/1', Method::Delete))
                ->with($this->createCapability(false)),
        ]);

        $rendered = $this->renderer->render($collection, $this->context)?->render();

        $this->assertStringContainsString('Edit', $rendered);
        $this->assertStringNotContainsString('Delete', $rendered);
    }

    public function test_can_render_returns_true_for_an_allowed_action(): void
    {
        $this->assertTrue($this->renderer->canRender(new Action(), $this->context));
    }

    public function test_can_render_returns_false_for_an_action_that_is_not_allowed(): void
    {
        $action = new Action()->with($this->createCapability(false));

        $this->assertFalse($this->renderer->canRender($action, $this->context));
    }

    public function test_can_render_returns_true_for_a_collection_with_a_renderable_action(): void
    {
        $collection = new ActionCollection([new Action()]);

        $this->assertTrue($this->renderer->canRender($collection, $this->context));
    }

    public function test_can_render_returns_false_for_an_empty_collection(): void
    {
        $this->assertFalse($this->renderer->canRender(new ActionCollection(), $this->context));
    }

    public function test_can_render_looks_into_nested_collections(): void
    {
        $collection = new ActionCollection([new ActionCollection([new Action()])]);

        $this->assertTrue($this->renderer->canRender($collection, $this->context));
    }

    public function test_can_render_is_evaluated_per_context(): void
    {
        $action = new Action()->with($this->createCapability(
            fn (ActionContext $context) => $context->isBulk,
        ));

        $this->assertFalse($this->renderer->canRender($action, $this->context));
        $this->assertTrue($this->renderer->canRender($action, $this->context->isBulk()));
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

    private function createCapability(bool|\Closure $check = true, ?\Closure $apply = null): ActionCapability
    {
        return new class($check, $apply) extends ActionCapability {
            public function __construct(
                private readonly bool|\Closure $checkValue,
                private readonly ?\Closure $applyCallback,
            ) {}

            public function check(ActionDescriptor $descriptor, ActionContext $context): bool
            {
                if ($this->checkValue instanceof \Closure) {
                    return (bool) call_user_func($this->checkValue, $context);
                }

                return $this->checkValue;
            }

            public function apply(ActionDescriptor $descriptor, ActionContext $context): void
            {
                if ($this->applyCallback !== null) {
                    call_user_func($this->applyCallback, $descriptor, $context);
                }
            }
        };
    }
}
