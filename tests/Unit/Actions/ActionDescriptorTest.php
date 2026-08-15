<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer;

/**
 * @internal
 */
#[CoversClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
class ActionDescriptorTest extends TestCase
{
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
    }

    public function test_action_descriptor_is_final_class(): void
    {
        $reflection = new \ReflectionClass(ActionDescriptor::class);

        $this->assertTrue($reflection->isFinal());
    }

    public function test_it_starts_with_an_empty_label(): void
    {
        $descriptor = new ActionDescriptor();

        $this->assertInstanceOf(LazyValue::class, $descriptor->label);
        $this->assertNull($descriptor->label->resolve($this->context));
    }

    public function test_it_starts_without_attributes(): void
    {
        $this->assertSame([], new ActionDescriptor()->attributes);
    }

    public function test_it_starts_without_an_intent(): void
    {
        $this->assertNull(new ActionDescriptor()->intent);
    }

    public function test_it_starts_with_three_empty_render_buffers(): void
    {
        $descriptor = new ActionDescriptor();

        $this->assertInstanceOf(RenderBuffer::class, $descriptor->beforeRender);
        $this->assertInstanceOf(RenderBuffer::class, $descriptor->attributesRender);
        $this->assertInstanceOf(RenderBuffer::class, $descriptor->afterRender);

        $this->assertSame('', $descriptor->beforeRender->render());
        $this->assertSame('', $descriptor->attributesRender->render());
        $this->assertSame('', $descriptor->afterRender->render());
    }

    public function test_the_render_buffers_are_separate_instances(): void
    {
        $descriptor = new ActionDescriptor();

        $this->assertNotSame($descriptor->beforeRender, $descriptor->attributesRender);
        $this->assertNotSame($descriptor->attributesRender, $descriptor->afterRender);
        $this->assertNotSame($descriptor->beforeRender, $descriptor->afterRender);
    }

    public function test_every_descriptor_gets_its_own_render_buffers(): void
    {
        $first  = new ActionDescriptor();
        $second = new ActionDescriptor();

        $this->assertNotSame($first->beforeRender, $second->beforeRender);
    }

    public function test_the_label_can_be_replaced(): void
    {
        $descriptor = new ActionDescriptor();

        $descriptor->label = new LazyValue('Edit');

        $this->assertSame('Edit', $descriptor->label->resolve($this->context));
    }

    public function test_attributes_can_be_added(): void
    {
        $descriptor = new ActionDescriptor();

        $descriptor->attributes['class'] = 'btn-danger';
        $descriptor->attributes['title'] = 'Delete';

        $this->assertSame(['class' => 'btn-danger', 'title' => 'Delete'], $descriptor->attributes);
    }

    public function test_the_intent_can_be_set(): void
    {
        $descriptor = new ActionDescriptor();

        $intent = new class extends ActionIntent {
            public function view(): string
            {
                return 'eloquent-tables::actions.http';
            }
        };

        $descriptor->intent = $intent;

        $this->assertSame($intent, $descriptor->intent);
    }

    public function test_empty_buffers_clears_the_content_of_every_buffer(): void
    {
        $descriptor = new ActionDescriptor();

        $descriptor->beforeRender->add('before');
        $descriptor->attributesRender->add('data-confirm="true"');
        $descriptor->afterRender->add('after');

        $descriptor->emptyBuffers();

        $this->assertSame('', $descriptor->beforeRender->render());
        $this->assertSame('', $descriptor->attributesRender->render());
        $this->assertSame('', $descriptor->afterRender->render());
    }

    public function test_empty_buffers_replaces_the_buffers_with_new_instances(): void
    {
        $descriptor = new ActionDescriptor();

        $before     = $descriptor->beforeRender;
        $attributes = $descriptor->attributesRender;
        $after      = $descriptor->afterRender;

        $descriptor->emptyBuffers();

        $this->assertNotSame($before, $descriptor->beforeRender);
        $this->assertNotSame($attributes, $descriptor->attributesRender);
        $this->assertNotSame($after, $descriptor->afterRender);
    }

    public function test_empty_buffers_keeps_the_label_the_attributes_and_the_intent(): void
    {
        $descriptor = new ActionDescriptor();

        $descriptor->label               = new LazyValue('Edit');
        $descriptor->attributes['class'] = 'btn-danger';

        $descriptor->emptyBuffers();

        $this->assertSame('Edit', $descriptor->label->resolve($this->context));
        $this->assertSame(['class' => 'btn-danger'], $descriptor->attributes);
    }

    public function test_empty_buffers_can_be_called_on_empty_buffers(): void
    {
        $descriptor = new ActionDescriptor();

        $descriptor->emptyBuffers();
        $descriptor->emptyBuffers();

        $this->assertSame('', $descriptor->beforeRender->render());
    }

    public function test_the_buffers_can_be_filled_again_after_being_emptied(): void
    {
        $descriptor = new ActionDescriptor();

        $descriptor->beforeRender->add('first');
        $descriptor->emptyBuffers();
        $descriptor->beforeRender->add('second');

        $this->assertSame('second', $descriptor->beforeRender->render());
    }
}
