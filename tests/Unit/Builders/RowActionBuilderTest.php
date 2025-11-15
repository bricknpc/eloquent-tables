<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\RowAction;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Builders\RowActionBuilder;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;

/**
 * @internal
 */
#[CoversClass(RowActionBuilder::class)]
#[UsesClass(Config::class)]
#[UsesClass(RowAction::class)]
#[UsesClass(Action::class)]
#[UsesClass(ButtonStyle::class)]
class RowActionBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var RowActionBuilder $builder */
        $builder = $this->app->make(RowActionBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction('Edit');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertSame('eloquent-tables::action.row-action', $view->name());
    }

    public function test_it_returns_null_when_authorization_fails(): void
    {
        /** @var RowActionBuilder $builder */
        $builder = $this->app->make(RowActionBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction('Edit')->authorize(fn () => false);

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertNull($view);
    }

    public function test_it_returns_null_when_when_fails(): void
    {
        /** @var RowActionBuilder $builder */
        $builder = $this->app->make(RowActionBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction('Edit')->when(fn () => false);

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertNull($view);
    }

    public function test_it_renders_the_correct_button_styles(): void
    {
        /** @var RowActionBuilder $builder */
        $builder = $this->app->make(RowActionBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction('Edit')->styles(ButtonStyle::Danger, ButtonStyle::Info);

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertSame('eloquent-tables::action.row-action', $view->name());
        $this->assertArrayHasKey('styles', $view->getData());
        $this->assertSame('btn-danger btn-info', $view->getData()['styles']);
    }

    public function test_it_calls_closure_for_action(): void
    {
        /** @var RowActionBuilder $builder */
        $builder = $this->app->make(RowActionBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(fn () => 'Closure called');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertSame('eloquent-tables::action.row-action', $view->name());
        $this->assertArrayHasKey('action', $view->getData());
        $this->assertSame('Closure called', $view->getData()['action']);
    }
}
