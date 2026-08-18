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
#[CoversClass(ActionIntent::class)]
#[UsesClass(ActionDescriptor::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
#[UsesClass(RenderBuffer::class)]
class ActionIntentTest extends TestCase
{
    private ActionDescriptor $descriptor;
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $this->descriptor = new ActionDescriptor();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
    }

    public function test_action_intent_is_abstract(): void
    {
        $reflection = new \ReflectionClass(ActionIntent::class);

        $this->assertTrue($reflection->isAbstract());
    }

    public function test_subclasses_provide_their_own_view(): void
    {
        $this->assertSame('tables.intents.my-intent', $this->createIntent()->view());
    }

    public function test_before_returns_the_intent_for_chaining(): void
    {
        $intent = $this->createIntent();

        $this->assertSame($intent, $intent->before(static function (): void {}));
    }

    public function test_after_returns_the_intent_for_chaining(): void
    {
        $intent = $this->createIntent();

        $this->assertSame($intent, $intent->after(static function (): void {}));
    }

    public function test_before_render_does_nothing_when_no_hook_is_set(): void
    {
        $intent = $this->createIntent();

        $intent->beforeRender($this->descriptor, $this->context);

        $this->assertSame([], $this->descriptor->attributes);
    }

    public function test_after_render_does_nothing_when_no_hook_is_set(): void
    {
        $intent = $this->createIntent();

        $intent->afterRender($this->descriptor, $this->context);

        $this->assertSame([], $this->descriptor->attributes);
    }

    public function test_before_render_calls_the_registered_hook(): void
    {
        $called = false;

        $intent = $this->createIntent()->before(static function () use (&$called): void {
            $called = true;
        });

        $intent->beforeRender($this->descriptor, $this->context);

        $this->assertTrue($called);
    }

    public function test_after_render_calls_the_registered_hook(): void
    {
        $called = false;

        $intent = $this->createIntent()->after(static function () use (&$called): void {
            $called = true;
        });

        $intent->afterRender($this->descriptor, $this->context);

        $this->assertTrue($called);
    }

    public function test_before_render_passes_the_descriptor_and_context_to_the_hook(): void
    {
        $descriptorPassed = null;
        $contextPassed    = null;

        $intent = $this->createIntent()->before(static function (
            ActionDescriptor $descriptor,
            ActionContext $context,
        ) use (&$descriptorPassed, &$contextPassed): void {
            $descriptorPassed = $descriptor;
            $contextPassed    = $context;
        });

        $intent->beforeRender($this->descriptor, $this->context);

        $this->assertSame($this->descriptor, $descriptorPassed);
        $this->assertSame($this->context, $contextPassed);
    }

    public function test_after_render_passes_the_descriptor_and_context_to_the_hook(): void
    {
        $descriptorPassed = null;
        $contextPassed    = null;

        $intent = $this->createIntent()->after(static function (
            ActionDescriptor $descriptor,
            ActionContext $context,
        ) use (&$descriptorPassed, &$contextPassed): void {
            $descriptorPassed = $descriptor;
            $contextPassed    = $context;
        });

        $intent->afterRender($this->descriptor, $this->context);

        $this->assertSame($this->descriptor, $descriptorPassed);
        $this->assertSame($this->context, $contextPassed);
    }

    public function test_the_before_hook_can_change_the_descriptor(): void
    {
        $intent = $this->createIntent()->before(static function (ActionDescriptor $descriptor): void {
            $descriptor->attributes['class'] = 'btn-danger';
        });

        $intent->beforeRender($this->descriptor, $this->context);

        $this->assertSame(['class' => 'btn-danger'], $this->descriptor->attributes);
    }

    public function test_the_hooks_are_independent_of_each_other(): void
    {
        $calls = [];

        $intent = $this
            ->createIntent()
            ->before(static function () use (&$calls): void {
                $calls[] = 'before';
            })
            ->after(static function () use (&$calls): void {
                $calls[] = 'after';
            });

        $intent->beforeRender($this->descriptor, $this->context);
        $intent->afterRender($this->descriptor, $this->context);

        $this->assertSame(['before', 'after'], $calls);
    }

    public function test_registering_a_hook_twice_keeps_the_last_one(): void
    {
        $calls = [];

        $intent = $this
            ->createIntent()
            ->before(static function () use (&$calls): void {
                $calls[] = 'first';
            })
            ->before(static function () use (&$calls): void {
                $calls[] = 'second';
            });

        $intent->beforeRender($this->descriptor, $this->context);

        $this->assertSame(['second'], $calls);
    }

    private function createIntent(): ActionIntent
    {
        return new class extends ActionIntent {
            public function view(): string
            {
                return 'tables.intents.my-intent';
            }
        };
    }
}
