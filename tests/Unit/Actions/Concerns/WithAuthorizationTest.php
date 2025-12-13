<?php

declare(strict_types=1);

namespace Actions\Concerns;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversTrait;
use BrickNPC\EloquentTables\Actions\Contracts\Authorizable;
use BrickNPC\EloquentTables\Actions\Concerns\WithAuthorization;

/**
 * @internal
 */
#[CoversTrait(WithAuthorization::class)]
class WithAuthorizationTest extends TestCase
{
    public function test_it_can_set_the_authorization_callback_through_property_or_fluent_setter(): void
    {
        $action = $this->action();
        $this->assertNull($action->authorize);

        $action2 = $this->action()->authorize(fn () => true);
        $this->assertInstanceOf(\Closure::class, $action2->authorize);

        $action3            = $this->action();
        $action3->authorize = fn () => true;
        $this->assertInstanceOf(\Closure::class, $action3->authorize);
    }

    public function test_empty_authorization_callback_means_always_authorized(): void
    {
        /** @var Request $request */
        $request = $this->app->make('request');

        $action = $this->action();

        $this->assertNull($action->authorize);
        $this->assertTrue($action->can($request));
    }

    public function test_it_can_check_if_the_action_is_authorized(): void
    {
        /** @var Request $request */
        $request = $this->app->make('request');

        $action = $this->action()->authorize(fn () => true);
        $this->assertTrue($action->can($request));

        $action2 = $this->action()->authorize(fn () => false);
        $this->assertFalse($action2->can($request));
    }

    private function action(): Authorizable
    {
        return new class implements Authorizable {
            use WithAuthorization;
        };
    }
}
