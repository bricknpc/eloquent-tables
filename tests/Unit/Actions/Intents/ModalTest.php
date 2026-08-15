<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Actions\Intents;

use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\Intents\Modal;
use BrickNPC\EloquentTables\ValueObjects\LazyValue;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

/**
 * @internal
 */
#[CoversClass(Modal::class)]
#[UsesClass(ActionIntent::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(Config::class)]
#[UsesClass(LazyValue::class)]
class ModalTest extends TestCase
{
    private ActionContext $context;

    protected function setUp(): void
    {
        parent::setUp();

        $request = $this->app->make('request');

        $config = $this->app->make(Config::class);

        $this->context = new ActionContext($request, $config);
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
}
