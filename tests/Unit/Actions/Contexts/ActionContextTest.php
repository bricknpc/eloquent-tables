<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Contexts;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @internal
 */
#[CoversClass(ActionContext::class)]
#[UsesClass(Config::class)]
class ActionContextTest extends TestCase
{
    private Request $request;
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = $this->app->make('request');
        $this->config  = $this->app->make(Config::class);
    }

    public function test_action_context_is_final_class(): void
    {
        $reflection = new \ReflectionClass(ActionContext::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_action_context_is_readonly(): void
    {
        $reflection = new \ReflectionClass(ActionContext::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_it_keeps_the_request_and_the_config(): void
    {
        $context = new ActionContext($this->request, $this->config);

        $this->assertSame($this->request, $context->request);
        $this->assertSame($this->config, $context->config);
    }

    public function test_the_model_defaults_to_null(): void
    {
        $context = new ActionContext($this->request, $this->config);

        $this->assertNull($context->model);
    }

    public function test_it_keeps_the_given_model(): void
    {
        $model = new TestModel();

        $context = new ActionContext($this->request, $this->config, $model);

        $this->assertSame($model, $context->model);
    }

    public function test_the_flags_default_to_false(): void
    {
        $context = new ActionContext($this->request, $this->config);

        $this->assertFalse($context->asDropdown);
        $this->assertFalse($context->isBulk);
    }

    public function test_the_flags_can_be_set_through_the_constructor(): void
    {
        $context = new ActionContext($this->request, $this->config, null, true, true);

        $this->assertTrue($context->asDropdown);
        $this->assertTrue($context->isBulk);
    }

    public function test_as_dropdown_returns_a_new_context(): void
    {
        $context = new ActionContext($this->request, $this->config);

        $dropdown = $context->asDropdown();

        $this->assertNotSame($context, $dropdown);
        $this->assertTrue($dropdown->asDropdown);
    }

    public function test_as_dropdown_leaves_the_original_context_untouched(): void
    {
        $context = new ActionContext($this->request, $this->config);

        $context->asDropdown();

        $this->assertFalse($context->asDropdown);
    }

    public function test_as_dropdown_keeps_the_other_values(): void
    {
        $model = new TestModel();

        $context = new ActionContext($this->request, $this->config, $model)->isBulk();

        $dropdown = $context->asDropdown();

        $this->assertSame($this->request, $dropdown->request);
        $this->assertSame($this->config, $dropdown->config);
        $this->assertSame($model, $dropdown->model);
        $this->assertTrue($dropdown->isBulk);
    }

    public function test_is_bulk_returns_a_new_context(): void
    {
        $context = new ActionContext($this->request, $this->config);

        $bulk = $context->isBulk();

        $this->assertNotSame($context, $bulk);
        $this->assertTrue($bulk->isBulk);
    }

    public function test_is_bulk_leaves_the_original_context_untouched(): void
    {
        $context = new ActionContext($this->request, $this->config);

        $context->isBulk();

        $this->assertFalse($context->isBulk);
    }

    public function test_is_bulk_keeps_the_other_values(): void
    {
        $model = new TestModel();

        $context = new ActionContext($this->request, $this->config, $model)->asDropdown();

        $bulk = $context->isBulk();

        $this->assertSame($this->request, $bulk->request);
        $this->assertSame($this->config, $bulk->config);
        $this->assertSame($model, $bulk->model);
        $this->assertTrue($bulk->asDropdown);
    }

    public function test_the_flags_can_be_combined(): void
    {
        $context = new ActionContext($this->request, $this->config)->asDropdown()->isBulk();

        $this->assertTrue($context->asDropdown);
        $this->assertTrue($context->isBulk);
    }

    public function test_calling_a_flag_twice_keeps_it_set(): void
    {
        $context = new ActionContext($this->request, $this->config)->isBulk()->isBulk();

        $this->assertTrue($context->isBulk);
    }
}
