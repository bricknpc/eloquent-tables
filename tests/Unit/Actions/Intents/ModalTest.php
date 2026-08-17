<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Intents;

use Illuminate\Support\HtmlString;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Enums\ActionRegion;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\Intents\Modal;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Styles\ActionStyleResolver;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(Modal::class)]
#[UsesClass(Action::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionRenderer::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(Theme::class)]
#[UsesClass(ActionRegion::class)]
#[UsesClass(ButtonStyle::class)]
#[UsesClass(ActionStyleResolver::class)]
class ModalTest extends TestCase
{
    private ActionContext $context;
    private ActionRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context  = new ActionContext($request, $config);
        $this->renderer = new ActionRenderer($config);
    }

    public function test_modal_is_instance_of_action_intent(): void
    {
        $intent = new Modal('Delete user');

        $this->assertInstanceOf(ActionIntent::class, $intent);
    }

    public function test_modal_is_final_class(): void
    {
        $reflection = new \ReflectionClass(Modal::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_view_returns_the_modal_action_view(): void
    {
        $intent = new Modal('Delete user');

        $this->assertSame('eloquent-tables::actions.modal', $intent->view());
    }

    public function test_the_title_is_kept_as_given(): void
    {
        $intent = new Modal('Delete user');

        $this->assertSame('Delete user', $intent->title);
    }

    public function test_the_content_defaults_to_null(): void
    {
        $intent = new Modal('Delete user');

        $this->assertNull($intent->content);
    }

    public function test_the_content_can_be_set(): void
    {
        $intent = new Modal('Delete user', 'This will permanently delete the user.');

        $this->assertSame('This will permanently delete the user.', $intent->content);
    }

    public function test_the_title_can_be_a_closure(): void
    {
        $title = fn (ActionContext $context) => 'Delete user';

        $intent = new Modal($title);

        $this->assertSame($title, $intent->title);
        $this->assertSame('Delete user', new LazyValue($intent->title)->resolve($this->context));
    }

    public function test_the_content_can_be_a_closure(): void
    {
        $content = fn (ActionContext $context) => $context->isBulk ? 'Delete all selected users' : 'Delete this user';

        $intent = new Modal('Delete user', $content);

        $this->assertSame('Delete this user', new LazyValue($intent->content)->resolve($this->context));
        $this->assertSame('Delete all selected users', new LazyValue($intent->content)->resolve($this->context->isBulk()));
    }

    public function test_the_properties_are_readonly(): void
    {
        $reflection = new \ReflectionClass(Modal::class);

        $this->assertTrue($reflection->getProperty('title')->isReadOnly());
        $this->assertTrue($reflection->getProperty('content')->isReadOnly());
    }

    public function test_title_returns_a_lazy_value(): void
    {
        $intent = new Modal('Delete user');

        $this->assertInstanceOf(LazyValue::class, $intent->title());
    }

    public function test_title_resolves_a_string_title(): void
    {
        $intent = new Modal('Delete user');

        $this->assertSame('Delete user', $intent->title()->resolve($this->context));
    }

    public function test_title_resolves_a_closure_title_with_the_context(): void
    {
        $intent = new Modal(fn (ActionContext $context) => $context->isBulk ? 'Delete users' : 'Delete user');

        $this->assertSame('Delete user', $intent->title()->resolve($this->context));
        $this->assertSame('Delete users', $intent->title()->resolve($this->context->isBulk()));
    }

    public function test_content_returns_a_lazy_value(): void
    {
        $intent = new Modal('Delete user');

        $this->assertInstanceOf(LazyValue::class, $intent->content());
    }

    public function test_content_resolves_to_null_when_no_content_is_given(): void
    {
        $intent = new Modal('Delete user');

        $this->assertNull($intent->content()->resolve($this->context));
    }

    public function test_content_resolves_a_string_content(): void
    {
        $intent = new Modal('Delete user', 'This will permanently delete the user.');

        $this->assertSame('This will permanently delete the user.', $intent->content()->resolve($this->context));
    }

    public function test_content_resolves_a_closure_content_with_the_context(): void
    {
        $intent = new Modal('Delete user', fn (ActionContext $context) => $context->isBulk ? 'All selected' : 'This one');

        $this->assertSame('This one', $intent->content()->resolve($this->context));
        $this->assertSame('All selected', $intent->content()->resolve($this->context->isBulk()));
    }

    public function test_it_renders_a_button_that_opens_the_modal(): void
    {
        $rendered = $this->render(new Modal('Delete user'));

        $this->assertStringContainsString('<button type="button"', $rendered);
        $this->assertStringContainsString('data-bs-toggle="modal"', $rendered);
        $this->assertMatchesRegularExpression('/data-bs-target="#modal-[a-f0-9]{32}"/', $rendered);
    }

    public function test_the_button_targets_the_modal_it_renders(): void
    {
        $rendered = $this->render(new Modal('Delete user'));

        preg_match('/data-bs-target="#(modal-[a-f0-9]{32})"/', $rendered, $matches);

        $this->assertArrayHasKey(1, $matches);
        $this->assertStringContainsString('id="' . $matches[1] . '"', $rendered);
    }

    public function test_it_renders_the_label_on_the_button(): void
    {
        $rendered = $this->render(new Modal('Delete user'), 'Delete');

        $this->assertStringContainsString('>Delete</button>', $rendered);
    }

    public function test_it_renders_the_title_in_the_modal_header(): void
    {
        $rendered = $this->render(new Modal('Delete user'));

        $this->assertStringContainsString('Delete user</h5>', $rendered);
    }

    public function test_it_renders_the_content_in_the_modal_body(): void
    {
        $rendered = $this->render(new Modal('Delete user', 'This will permanently delete the user.'));

        $this->assertStringContainsString('This will permanently delete the user.', $rendered);
    }

    public function test_it_renders_html_content_as_html(): void
    {
        $rendered = $this->render(new Modal('Delete user', new HtmlString('<p>Gone forever</p>')->toHtml()));

        $this->assertStringContainsString('<p>Gone forever</p>', $rendered);
    }

    public function test_it_renders_an_empty_body_without_content(): void
    {
        $rendered = $this->render(new Modal('Delete user'));

        $this->assertStringContainsString('modal-body', $rendered);
    }

    public function test_the_modal_is_labelled_by_its_title(): void
    {
        $rendered = $this->render(new Modal('Delete user'));

        preg_match('/aria-labelledby="(modal-[a-f0-9]{32}-title)"/', $rendered, $matches);

        $this->assertArrayHasKey(1, $matches);
        $this->assertStringContainsString('id="' . $matches[1] . '"', $rendered);
    }

    public function test_the_modal_can_be_dismissed(): void
    {
        $rendered = $this->render(new Modal('Delete user'));

        $this->assertStringContainsString('class="btn-close" data-bs-dismiss="modal"', $rendered);
        $this->assertStringContainsString('Close</button>', $rendered);
    }

    public function test_it_renders_the_button_as_a_dropdown_item_inside_a_dropdown(): void
    {
        $rendered = $this->render(new Modal('Delete user'), context: $this->context->asDropdown());

        $this->assertStringContainsString('class="dropdown-item"', $rendered);
        $this->assertStringNotContainsString('btn btn-primary', $rendered);
    }

    public function test_every_rendered_modal_gets_its_own_id(): void
    {
        $intent = new Modal('Delete user');

        preg_match('/id="(modal-[a-f0-9]{32})"/', $this->render($intent), $first);
        preg_match('/id="(modal-[a-f0-9]{32})"/', $this->render($intent), $second);

        $this->assertNotSame($first[1], $second[1]);
    }

    private function render(Modal $intent, string $label = 'Open', ?ActionContext $context = null): string
    {
        $action = new Action()->label($label)->as($intent);

        return (string) $this->renderer->render($action, $context ?? $this->context)?->render();
    }
}
