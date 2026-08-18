<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Intents;

use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Enums\ActionRegion;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Intents\HttpModal;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Styles\ActionStyleResolver;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(HttpModal::class)]
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
class HttpModalTest extends TestCase
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

    public function test_http_modal_is_instance_of_action_intent(): void
    {
        $intent = new HttpModal('Edit user', 'https://example.com/users/1/edit');

        $this->assertInstanceOf(ActionIntent::class, $intent);
    }

    public function test_http_modal_is_final_class(): void
    {
        $reflection = new \ReflectionClass(HttpModal::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_view_returns_the_http_modal_action_view(): void
    {
        $intent = new HttpModal('Edit user', 'https://example.com/users/1/edit');

        $this->assertSame('eloquent-tables::actions.http-modal', $intent->view());
    }

    public function test_the_title_is_kept_as_given(): void
    {
        $intent = new HttpModal('Edit user', 'https://example.com/users/1/edit');

        $this->assertSame('Edit user', $intent->title);
    }

    public function test_url_returns_a_lazy_value(): void
    {
        $intent = new HttpModal('Edit user', 'https://example.com/users/1/edit');

        $this->assertInstanceOf(LazyValue::class, $intent->url());
    }

    public function test_url_resolves_a_string_url(): void
    {
        $intent = new HttpModal('Edit user', 'https://example.com/users/1/edit');

        $this->assertSame('https://example.com/users/1/edit', $intent->url()->resolve($this->context));
    }

    public function test_url_resolves_a_closure_url_with_the_model_from_the_context(): void
    {
        $model     = new TestModel();
        $model->id = 7;

        $context = new ActionContext($this->context->request, $this->context->config, $model);

        $intent = new HttpModal(
            'Edit user',
            static fn (ActionContext $context) => 'https://example.com/users/' . $context->model?->getKey() . '/edit',
        );

        $this->assertSame('https://example.com/users/7/edit', $intent->url()->resolve($context));
    }

    public function test_the_title_can_be_a_closure(): void
    {
        $intent = new HttpModal(
            static fn (ActionContext $context) => $context->isBulk ? 'Edit users' : 'Edit user',
            'https://example.com/users/1/edit',
        );

        $this->assertSame('Edit user', new LazyValue($intent->title)->resolve($this->context));
        $this->assertSame('Edit users', new LazyValue($intent->title)->resolve($this->context->isBulk()));
    }

    public function test_the_url_property_keeps_the_raw_value(): void
    {
        $intent = new HttpModal('Edit user', 'https://example.com/users/1/edit');

        $this->assertSame('https://example.com/users/1/edit', $intent->url);
    }

    public function test_the_properties_are_readonly(): void
    {
        $reflection = new \ReflectionClass(HttpModal::class);

        $this->assertTrue($reflection->getProperty('title')->isReadOnly());
        $this->assertTrue($reflection->getProperty('url')->isReadOnly());
    }

    public function test_title_returns_a_lazy_value(): void
    {
        $intent = new HttpModal('Edit user', 'https://example.com/users/1/edit');

        $this->assertInstanceOf(LazyValue::class, $intent->title());
    }

    public function test_title_resolves_a_string_title(): void
    {
        $intent = new HttpModal('Edit user', 'https://example.com/users/1/edit');

        $this->assertSame('Edit user', $intent->title()->resolve($this->context));
    }

    public function test_title_resolves_a_closure_title_with_the_context(): void
    {
        $intent = new HttpModal(
            static fn (ActionContext $context) => $context->isBulk ? 'Edit users' : 'Edit user',
            'https://example.com/users/1/edit',
        );

        $this->assertSame('Edit user', $intent->title()->resolve($this->context));
        $this->assertSame('Edit users', $intent->title()->resolve($this->context->isBulk()));
    }

    public function test_it_renders_a_button_that_opens_the_modal(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        $this->assertStringContainsString('data-bs-toggle="modal"', $rendered);
        $this->assertMatchesRegularExpression('/data-bs-target="#modal-[a-f0-9]{32}"/', $rendered);
    }

    public function test_the_button_targets_the_modal_it_renders(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        preg_match('/data-bs-target="#(modal-[a-f0-9]{32})"/', $rendered, $matches);

        $this->assertArrayHasKey(1, $matches);
        $this->assertStringContainsString('id="' . $matches[1] . '"', $rendered);
    }

    public function test_it_puts_the_url_on_the_frame_for_the_javascript(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        $this->assertStringContainsString('data-et-modal-src="https://example.com/users/1/edit"', $rendered);
    }

    public function test_the_frame_has_no_source_until_the_modal_is_opened(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        $this->assertStringContainsString('<iframe', $rendered);
        $this->assertDoesNotMatchRegularExpression('/<iframe[^>]*\ssrc=/', $rendered);
    }

    public function test_it_resolves_a_closure_url_with_the_model_from_the_context(): void
    {
        $model     = new TestModel();
        $model->id = 7;

        $context = new ActionContext($this->context->request, $this->context->config, $model);

        $intent = new HttpModal(
            'Edit user',
            static fn (ActionContext $context) => 'https://example.com/users/' . $context->model?->getKey() . '/edit',
        );

        $rendered = $this->render($intent, context: $context);

        $this->assertStringContainsString('data-et-modal-src="https://example.com/users/7/edit"', $rendered);
    }

    public function test_it_uses_the_configured_data_namespace(): void
    {
        $this->app->make('config')->set('eloquent-tables.data-namespace', 'tables');

        $renderer = new ActionRenderer($this->app->make(Config::class));

        $action = new Action()
            ->label('Edit')
            ->as(new HttpModal('Edit user', 'https://example.com/users/1/edit'))
        ;

        $rendered = (string) $renderer->render($action, $this->context)?->render();

        $this->assertStringContainsString('data-tables-modal-frame="true"', $rendered);
        $this->assertStringContainsString('data-tables-modal-src=', $rendered);
        $this->assertStringContainsString('data-tables-modal-loading="true"', $rendered);
        $this->assertStringContainsString('data-tables-modal-error="true"', $rendered);
    }

    public function test_it_renders_the_title_in_the_modal_header(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        $this->assertStringContainsString('Edit user</h5>', $rendered);
    }

    public function test_the_frame_is_titled_after_the_modal(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        $this->assertStringContainsString('title="Edit user"', $rendered);
    }

    public function test_the_frame_title_does_not_contain_the_markup_of_the_title(): void
    {
        $rendered = $this->render(new HttpModal('<em>Edit</em> user', 'https://example.com/users/1/edit'));

        $this->assertStringContainsString('title="Edit user"', $rendered);
        $this->assertStringContainsString('<em>Edit</em> user</h5>', $rendered);
    }

    public function test_it_renders_a_hidden_frame_with_a_loading_indicator(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        $this->assertStringContainsString('data-et-modal-loading="true"', $rendered);
        $this->assertStringContainsString('spinner-border', $rendered);
        $this->assertMatchesRegularExpression('/<iframe[^>]*\shidden/', $rendered);
    }

    public function test_it_renders_a_hidden_error_message(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        $this->assertStringContainsString('data-et-modal-error="true" hidden', $rendered);
        $this->assertStringContainsString('The content of this modal could not be loaded.', $rendered);
    }

    public function test_the_modal_is_labelled_by_its_title(): void
    {
        $rendered = $this->render(new HttpModal('Edit user', 'https://example.com/users/1/edit'));

        preg_match('/aria-labelledby="(modal-[a-f0-9]{32}-title)"/', $rendered, $matches);

        $this->assertArrayHasKey(1, $matches);
        $this->assertStringContainsString('id="' . $matches[1] . '"', $rendered);
    }

    public function test_it_renders_the_button_as_a_dropdown_item_inside_a_dropdown(): void
    {
        $rendered = $this->render(
            new HttpModal('Edit user', 'https://example.com/users/1/edit'),
            context: $this->context->asDropdown(),
        );

        $this->assertStringContainsString('class="dropdown-item"', $rendered);
    }

    private function render(HttpModal $intent, string $label = 'Edit', ?ActionContext $context = null): string
    {
        $action = new Action()->label($label)->as($intent);

        return (string) $this->renderer->render($action, $context ?? $this->context)?->render();
    }
}
