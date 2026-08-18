<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Intents;

use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Enums\ActionRegion;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Styles\ActionStyleResolver;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(Http::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
#[UsesClass(ActionRegion::class)]
#[UsesClass(ButtonStyle::class)]
#[UsesClass(ActionStyleResolver::class)]
class HttpTest extends TestCase
{
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
    }

    public function test_http_is_instance_of_action_intent(): void
    {
        $intent = new Http('https://example.com/users');

        $this->assertInstanceOf(ActionIntent::class, $intent);
    }

    public function test_http_is_final_class(): void
    {
        $reflection = new \ReflectionClass(Http::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_view_returns_the_http_action_view(): void
    {
        $intent = new Http('https://example.com/users');

        $this->assertSame('eloquent-tables::actions.http', $intent->view());
    }

    public function test_the_method_defaults_to_get(): void
    {
        $intent = new Http('https://example.com/users');

        $this->assertSame(Method::Get, $intent->method);
    }

    public function test_the_method_can_be_set(): void
    {
        $intent = new Http('https://example.com/users', Method::Delete);

        $this->assertSame(Method::Delete, $intent->method);
    }

    public function test_url_returns_a_lazy_value(): void
    {
        $intent = new Http('https://example.com/users');

        $this->assertInstanceOf(LazyValue::class, $intent->url());
    }

    public function test_url_resolves_a_string_url(): void
    {
        $intent = new Http('https://example.com/users');

        $this->assertSame('https://example.com/users', $intent->url()->resolve($this->context));
    }

    public function test_url_resolves_a_closure_url_with_the_context(): void
    {
        $intent = new Http(static fn (ActionContext $context) => 'https://example.com/users/' . ($context->model?->getKey() ?? 'all'));

        $this->assertSame('https://example.com/users/all', $intent->url()->resolve($this->context));
    }

    public function test_url_resolves_a_closure_url_with_the_model_from_the_context(): void
    {
        $model     = new TestModel();
        $model->id = 42;

        $context = new ActionContext($this->context->request, $this->context->config, $model);

        $intent = new Http(static fn (ActionContext $context) => 'https://example.com/users/' . $context->model?->getKey());

        $this->assertSame('https://example.com/users/42', $intent->url()->resolve($context));
    }

    public function test_url_is_resolved_on_every_call(): void
    {
        $callCount = 0;

        $intent = new Http(static function (ActionContext $context) use (&$callCount): string {
            ++$callCount;

            return 'https://example.com/users/' . $callCount;
        });

        $this->assertSame('https://example.com/users/1', $intent->url()->resolve($this->context));
        $this->assertSame('https://example.com/users/2', $intent->url()->resolve($this->context));
    }

    public function test_the_url_property_keeps_the_raw_value(): void
    {
        $intent = new Http('https://example.com/users');

        $this->assertSame('https://example.com/users', $intent->url);
    }

    public function test_the_properties_are_readonly(): void
    {
        $reflection = new \ReflectionClass(Http::class);

        $this->assertTrue($reflection->getProperty('url')->isReadOnly());
        $this->assertTrue($reflection->getProperty('method')->isReadOnly());
    }

    public function test_the_render_hooks_are_available(): void
    {
        $called = false;

        $intent = new Http('https://example.com/users')->before(static function () use (&$called): void {
            $called = true;
        });

        $this->assertInstanceOf(Http::class, $intent);

        $intent->beforeRender(new ActionDescriptor(), $this->context);

        $this->assertTrue($called);
    }
}
