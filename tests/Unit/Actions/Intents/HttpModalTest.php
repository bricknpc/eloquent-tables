<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Intents;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\Intents\HttpModal;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @internal
 */
#[CoversClass(HttpModal::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
class HttpModalTest extends TestCase
{
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
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
            fn (ActionContext $context) => 'https://example.com/users/' . $context->model?->getKey() . '/edit',
        );

        $this->assertSame('https://example.com/users/7/edit', $intent->url()->resolve($context));
    }

    public function test_the_title_can_be_a_closure(): void
    {
        $intent = new HttpModal(
            fn (ActionContext $context) => $context->isBulk ? 'Edit users' : 'Edit user',
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
}
