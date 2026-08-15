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
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
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
