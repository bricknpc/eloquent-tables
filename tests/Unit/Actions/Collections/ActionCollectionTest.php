<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Collections;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use PHPUnit\Framework\Attributes\DataProvider;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

/**
 * @internal
 */
#[CoversClass(ActionCollection::class)]
#[UsesClass(Action::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(ActionCollectionType::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(StyleSet::class)]
class ActionCollectionTest extends TestCase
{
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
    }

    public function test_style_returns_the_collection_for_chaining(): void
    {
        $collection = new ActionCollection();

        $this->assertSame($collection, $collection->style(ButtonStyle::Danger));
    }

    public function test_a_new_collection_has_no_style(): void
    {
        $this->assertNull(new ActionCollection()->style);
    }

    public function test_style_keeps_the_declared_styles(): void
    {
        $collection = new ActionCollection()->style(ButtonStyle::Danger);

        $this->assertSame([ButtonStyle::Danger], $collection->style?->resolve($this->context));
    }

    public function test_a_second_style_call_merges_rather_than_replaces(): void
    {
        $collection = new ActionCollection()->style(ButtonStyle::Danger)->style(ButtonStyle::Link);

        $this->assertSame([ButtonStyle::Danger, ButtonStyle::Link], $collection->style?->resolve($this->context));
    }

    public function test_style_accepts_a_closure_that_receives_the_context(): void
    {
        $received = null;

        $collection = new ActionCollection()->style(static function ($given) use (&$received) {
            $received = $given;

            return ButtonStyle::Danger;
        });

        $this->assertSame([ButtonStyle::Danger], $collection->style?->resolve($this->context));
        $this->assertSame($this->context, $received);
    }

    public function test_style_survives_becoming_a_dropdown(): void
    {
        $collection = new ActionCollection()->style(ButtonStyle::Danger)->dropdown();

        $this->assertSame([ButtonStyle::Danger], $collection->style?->resolve($this->context));
    }

    public function test_style_cannot_be_set_from_outside_the_collection(): void
    {
        $collection = new ActionCollection();

        $this->expectException(\Error::class);

        // @phpstan-ignore-next-line
        $collection->style = new StyleSet(ButtonStyle::Danger);
    }

    public function test_a_filtered_collection_keeps_neither_its_type_nor_its_style(): void
    {
        // Covers R-4. A transform builds a plain collection, so style it after transforming, not before.
        $collection = new ActionCollection([new Action()])->dropdown()->style(ButtonStyle::Danger);

        $filtered = $collection->filter(static fn () => true);

        $this->assertSame(ActionCollectionType::Normal, $filtered->type);
        $this->assertNull($filtered->style);
    }

    public function test_action_collection_is_a_laravel_collection(): void
    {
        $this->assertInstanceOf(Collection::class, new ActionCollection());
    }

    public function test_an_empty_collection_can_be_created(): void
    {
        $collection = new ActionCollection();

        $this->assertCount(0, $collection);
    }

    public function test_the_collection_holds_the_given_actions(): void
    {
        $first  = new Action();
        $second = new Action();

        $collection = new ActionCollection([$first, $second]);

        $this->assertCount(2, $collection);
        $this->assertSame($first, $collection->first());
    }

    public function test_the_type_defaults_to_normal(): void
    {
        $this->assertSame(ActionCollectionType::Normal, new ActionCollection()->type);
    }

    public function test_the_type_can_be_set_through_the_constructor(): void
    {
        $collection = new ActionCollection([], ActionCollectionType::Dropdown);

        $this->assertSame(ActionCollectionType::Dropdown, $collection->type);
    }

    public function test_a_null_type_falls_back_to_normal(): void
    {
        $collection = new ActionCollection([], null);

        $this->assertSame(ActionCollectionType::Normal, $collection->type);
    }

    public function test_type_returns_a_new_collection(): void
    {
        $collection = new ActionCollection();

        $dropdown = $collection->type(ActionCollectionType::Dropdown);

        $this->assertNotSame($collection, $dropdown);
        $this->assertSame(ActionCollectionType::Dropdown, $dropdown->type);
    }

    public function test_type_leaves_the_original_collection_untouched(): void
    {
        $collection = new ActionCollection();

        $collection->type(ActionCollectionType::Grouped);

        $this->assertSame(ActionCollectionType::Normal, $collection->type);
    }

    public function test_type_keeps_the_actions(): void
    {
        $collection = new ActionCollection([new Action(), new Action()]);

        $this->assertCount(2, $collection->type(ActionCollectionType::Grouped));
    }

    public function test_the_label_defaults_to_null(): void
    {
        $this->assertNull(new ActionCollection()->label);
    }

    public function test_label_sets_a_string_label(): void
    {
        $collection = new ActionCollection()->label('User actions');

        $this->assertSame('User actions', $collection->label);
    }

    public function test_label_returns_the_collection_for_chaining(): void
    {
        $collection = new ActionCollection();

        $this->assertSame($collection, $collection->label('User actions'));
    }

    public function test_label_accepts_a_closure(): void
    {
        $label = static fn (ActionContext $context) => 'User actions';

        $collection = new ActionCollection()->label($label);

        $this->assertSame($label, $collection->label);
        $this->assertSame('User actions', new LazyValue($collection->label)->resolve($this->context));
    }

    public function test_count_renderable_counts_every_action(): void
    {
        $collection = new ActionCollection([new Action(), new Action()]);

        $this->assertSame(2, $collection->countRenderable($this->context));
    }

    public function test_count_renderable_skips_actions_that_can_not_be_rendered(): void
    {
        $collection = new ActionCollection([
            new Action(),
            new Action()->with($this->createCapability(false)),
        ]);

        $this->assertSame(1, $collection->countRenderable($this->context));
    }

    public function test_count_renderable_returns_zero_for_an_empty_collection(): void
    {
        $this->assertSame(0, new ActionCollection()->countRenderable($this->context));
    }

    public function test_count_renderable_counts_the_actions_of_nested_collections(): void
    {
        $collection = new ActionCollection([
            new Action(),
            new ActionCollection([new Action(), new Action()]),
        ]);

        $this->assertSame(3, $collection->countRenderable($this->context));
    }

    public function test_count_renderable_skips_hidden_actions_in_nested_collections(): void
    {
        $collection = new ActionCollection([
            new Action(),
            new ActionCollection([
                new Action(),
                new Action()->with($this->createCapability(false)),
            ]),
        ]);

        $this->assertSame(2, $collection->countRenderable($this->context));
    }

    public function test_count_renderable_is_evaluated_per_context(): void
    {
        $collection = new ActionCollection([
            new Action()->with($this->createCapability(static fn (ActionContext $context) => $context->isBulk)),
        ]);

        $this->assertSame(0, $collection->countRenderable($this->context));
        $this->assertSame(1, $collection->countRenderable($this->context->isBulk()));
    }

    public function test_has_renderable_returns_true_when_an_action_can_be_rendered(): void
    {
        $collection = new ActionCollection([new Action()]);

        $this->assertTrue($collection->hasRenderable($this->context));
    }

    public function test_has_renderable_returns_false_for_an_empty_collection(): void
    {
        $this->assertFalse(new ActionCollection()->hasRenderable($this->context));
    }

    public function test_has_renderable_returns_false_when_no_action_can_be_rendered(): void
    {
        $collection = new ActionCollection([
            new Action()->with($this->createCapability(false)),
            new Action()->with($this->createCapability(false)),
        ]);

        $this->assertFalse($collection->hasRenderable($this->context));
    }

    public function test_has_renderable_looks_into_nested_collections(): void
    {
        $collection = new ActionCollection([
            new ActionCollection([new Action()]),
        ]);

        $this->assertTrue($collection->hasRenderable($this->context));
    }

    public function test_normal_creates_a_normal_collection(): void
    {
        $collection = new ActionCollection()->normal(new Action(), new Action());

        $this->assertSame(ActionCollectionType::Normal, $collection->type);
        $this->assertCount(2, $collection);
    }

    public function test_group_creates_a_grouped_collection(): void
    {
        $collection = new ActionCollection()->group(new Action());

        $this->assertSame(ActionCollectionType::Grouped, $collection->type);
        $this->assertCount(1, $collection);
    }

    public function test_dropdown_creates_a_dropdown_collection(): void
    {
        $collection = new ActionCollection()->dropdown(new Action());

        $this->assertSame(ActionCollectionType::Dropdown, $collection->type);
        $this->assertCount(1, $collection);
    }

    /**
     * @param non-empty-string $method
     */
    #[DataProvider('typeMethodProvider')]
    public function test_it_keeps_the_existing_actions_when_no_arguments_are_given(string $method, ActionCollectionType $expected): void
    {
        $first  = new Action();
        $second = new Action();

        $collection = new ActionCollection([$first, $second])->{$method}();

        $this->assertSame($expected, $collection->type);
        $this->assertCount(2, $collection);
        $this->assertSame([$first, $second], $collection->all());
    }

    /**
     * @param non-empty-string $method
     */
    #[DataProvider('typeMethodProvider')]
    public function test_it_appends_the_given_actions_to_the_existing_ones(string $method, ActionCollectionType $expected): void
    {
        $existing = new Action();
        $added    = new Action();

        $collection = new ActionCollection([$existing])->{$method}($added);

        $this->assertSame($expected, $collection->type);
        $this->assertSame([$existing, $added], $collection->all());
    }

    /**
     * @param non-empty-string $method
     */
    #[DataProvider('typeMethodProvider')]
    public function test_it_leaves_the_original_collection_untouched(string $method, ActionCollectionType $expected): void
    {
        $original = new ActionCollection([new Action()]);

        $result = $original->{$method}(new Action());

        $this->assertNotSame($original, $result);
        $this->assertCount(1, $original);
        $this->assertSame(ActionCollectionType::Normal, $original->type);
        $this->assertCount(2, $result);
    }

    /**
     * @param non-empty-string $method
     */
    #[DataProvider('typeMethodProvider')]
    public function test_it_keeps_the_label(string $method, ActionCollectionType $expected): void
    {
        $collection = new ActionCollection([new Action()])->label('Open')->{$method}();

        $this->assertSame('Open', $collection->label);
    }

    /**
     * @return \Generator<string, array{non-empty-string, ActionCollectionType}>
     */
    public static function typeMethodProvider(): \Generator
    {
        yield 'normal' => ['normal', ActionCollectionType::Normal];

        yield 'group' => ['group', ActionCollectionType::Grouped];

        yield 'dropdown' => ['dropdown', ActionCollectionType::Dropdown];
    }

    private function createCapability(bool|\Closure $check): ActionCapability
    {
        return new class($check) extends ActionCapability {
            public function __construct(private readonly bool|\Closure $checkValue) {}

            public function check(ActionDescriptor $descriptor, ActionContext $context): bool
            {
                if ($this->checkValue instanceof \Closure) {
                    return (bool) call_user_func($this->checkValue, $context);
                }

                return $this->checkValue;
            }
        };
    }
}
