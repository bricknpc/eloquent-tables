<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Capabilities;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Actions\Capabilities\Style;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(Style::class)]
#[UsesClass(Action::class)]
#[UsesClass(ActionCapability::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(ActionRenderer::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(ButtonStyle::class)]
#[UsesClass(Config::class)]
#[UsesClass(Http::class)]
#[UsesClass(Method::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(Theme::class)]
#[UsesClass(StyleSet::class)]
class StyleTest extends TestCase
{
    private ActionDescriptor $descriptor;
    private ActionContext $context;
    private ActionRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->descriptor = new ActionDescriptor();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context  = new ActionContext($request, $config);
        $this->renderer = new ActionRenderer($config);
    }

    public function test_style_is_instance_of_action_capability(): void
    {
        $this->assertInstanceOf(ActionCapability::class, new Style(ButtonStyle::Danger));
    }

    public function test_style_is_final_class(): void
    {
        $reflection = new \ReflectionClass(Style::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_apply_puts_the_css_class_of_the_style_on_the_descriptor(): void
    {
        new Style(ButtonStyle::Danger)->apply($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-danger'], $this->descriptor->attributes);
    }

    public function test_apply_combines_multiple_styles(): void
    {
        new Style(ButtonStyle::Danger, ButtonStyle::Link)->apply($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-danger btn-link'], $this->descriptor->attributes);
    }

    public function test_apply_uses_the_dropdown_variant_inside_a_dropdown(): void
    {
        new Style(ButtonStyle::Danger)->apply($this->descriptor, $this->context->asDropdown());

        $this->assertSame(['class' => 'text-danger'], $this->descriptor->attributes);
    }

    public function test_apply_does_nothing_without_styles(): void
    {
        new Style()->apply($this->descriptor, $this->context);

        $this->assertSame([], $this->descriptor->attributes);
    }

    public function test_apply_does_nothing_for_the_default_style(): void
    {
        new Style(ButtonStyle::Default)->apply($this->descriptor, $this->context);

        $this->assertSame([], $this->descriptor->attributes);
    }

    public function test_apply_skips_the_default_style_between_other_styles(): void
    {
        new Style(ButtonStyle::Default, ButtonStyle::Success)->apply($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-success'], $this->descriptor->attributes);
    }

    public function test_apply_overwrites_a_class_of_an_earlier_capability(): void
    {
        $this->descriptor->attributes['class'] = 'btn-primary';

        new Style(ButtonStyle::Danger)->apply($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-danger'], $this->descriptor->attributes);
    }

    public function test_apply_leaves_other_attributes_alone(): void
    {
        $this->descriptor->attributes['title'] = 'Delete';

        new Style(ButtonStyle::Danger)->apply($this->descriptor, $this->context);

        $this->assertSame(['title' => 'Delete', 'class' => 'btn-danger'], $this->descriptor->attributes);
    }

    public function test_a_closure_adds_its_style_to_the_static_ones(): void
    {
        // Covers AE1.
        new Style(ButtonStyle::Danger, fn () => ButtonStyle::Link)->apply($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-danger btn-link'], $this->descriptor->attributes);
    }

    public function test_a_closure_may_be_declared_before_the_static_styles(): void
    {
        // Covers AE3.
        new Style(fn () => ButtonStyle::Link, ButtonStyle::Danger)->apply($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-danger btn-link'], $this->descriptor->attributes);
    }

    public function test_a_closure_returning_several_styles_adds_all_of_them(): void
    {
        new Style(fn () => [ButtonStyle::Danger, ButtonStyle::Link])->apply($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-danger btn-link'], $this->descriptor->attributes);
    }

    public function test_a_closure_returning_nothing_leaves_the_static_styles_alone(): void
    {
        new Style(ButtonStyle::Danger, fn () => null)->apply($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-danger'], $this->descriptor->attributes);
    }

    public function test_the_closure_receives_the_action_context(): void
    {
        // Covers AE2.
        $received = null;

        new Style(function ($given) use (&$received) {
            $received = $given;

            return null;
        })->apply($this->descriptor, $this->context);

        $this->assertSame($this->context, $received);
        $this->assertNull($received->model);
    }

    public function test_the_closure_receives_the_model_of_a_row_action(): void
    {
        $model    = new TestModel();
        $received = null;

        $context = new ActionContext($this->context->request, $this->context->config, $model);

        new Style(function ($given) use (&$received) {
            $received = $given;

            return null;
        })->apply($this->descriptor, $context);

        $this->assertSame($model, $received->model);
    }

    public function test_a_closure_supplied_style_uses_the_dropdown_variant_inside_a_dropdown(): void
    {
        // Covers AE4.
        new Style(fn () => ButtonStyle::Danger)->apply($this->descriptor, $this->context->asDropdown());

        $this->assertSame(['class' => 'text-danger'], $this->descriptor->attributes);
    }

    public function test_a_closure_returning_only_the_default_style_leaves_the_descriptor_untouched(): void
    {
        new Style(fn () => ButtonStyle::Default)->apply($this->descriptor, $this->context);

        $this->assertSame([], $this->descriptor->attributes);
    }

    public function test_a_closure_may_decide_the_style_from_the_context(): void
    {
        $style = new Style(fn (ActionContext $context) => $context->isBulk ? ButtonStyle::Danger : ButtonStyle::Link);

        $style->apply($this->descriptor, $this->context->isBulk());

        $this->assertSame(['class' => 'btn-danger'], $this->descriptor->attributes);

        $descriptor = new ActionDescriptor();

        $style->apply($descriptor, $this->context);

        $this->assertSame(['class' => 'btn-link'], $descriptor->attributes);
    }

    public function test_it_styles_a_rendered_action_from_a_closure(): void
    {
        $rendered = $this->render(new Style(fn () => ButtonStyle::DangerOutline));

        $this->assertStringContainsString('class="btn btn-outline-danger"', $rendered);
    }

    public function test_check_returns_true(): void
    {
        $this->assertTrue(new Style(ButtonStyle::Danger)->check($this->descriptor, $this->context));
    }

    public function test_contribute_returns_null(): void
    {
        $this->assertNull(new Style(ButtonStyle::Danger)->contribute($this->descriptor, $this->context));
    }

    public function test_it_replaces_the_default_class_of_a_rendered_action(): void
    {
        $rendered = $this->render(new Style(ButtonStyle::DangerOutline));

        $this->assertStringContainsString('class="btn btn-outline-danger"', $rendered);
        $this->assertStringNotContainsString('btn-primary', $rendered);
    }

    public function test_an_action_without_a_style_keeps_the_default_class(): void
    {
        $rendered = $this->render();

        $this->assertStringContainsString('class="btn btn-primary"', $rendered);
    }

    public function test_it_styles_a_rendered_action_inside_a_dropdown(): void
    {
        $rendered = $this->render(new Style(ButtonStyle::Danger), $this->context->asDropdown());

        $this->assertStringContainsString('class="dropdown-item text-danger"', $rendered);
    }

    public function test_an_action_inside_a_dropdown_without_a_style_stays_a_dropdown_item(): void
    {
        $rendered = $this->render(context: $this->context->asDropdown());

        $this->assertStringContainsString('class="dropdown-item', $rendered);
        $this->assertStringNotContainsString('btn-primary', $rendered);
    }

    public function test_it_styles_a_form_action(): void
    {
        $action = new Action()
            ->label('Delete')
            ->as(new Http('https://example.com/users/1', Method::Delete))
            ->with(new Style(ButtonStyle::Danger))
        ;

        $rendered = (string) $this->renderer->render($action, $this->context)?->render();

        $this->assertStringContainsString('class="btn btn-danger"', $rendered);
    }

    private function render(?Style $style = null, ?ActionContext $context = null): string
    {
        $action = new Action()->label('Delete')->as(new Http('https://example.com/users/1/delete'));

        if ($style !== null) {
            $action->with($style);
        }

        return (string) $this->renderer->render($action, $context ?? $this->context)?->render();
    }
}
