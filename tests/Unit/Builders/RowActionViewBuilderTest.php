<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\RowAction;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Builders\RowActionViewBuilder;

/**
 * @internal
 */
#[CoversClass(RowActionViewBuilder::class)]
#[UsesClass(Config::class)]
#[UsesClass(RowAction::class)]
#[UsesClass(Action::class)]
#[UsesClass(ButtonStyle::class)]
class RowActionViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction('Edit');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertSame('eloquent-tables::action.row-action', $view->name());
    }

    public function test_it_returns_null_when_authorization_fails(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction('Edit')->authorize(fn () => false);

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertNull($view);
    }

    public function test_it_returns_null_when_when_fails(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction('Edit')->when(fn () => false);

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertNull($view);
    }

    public function test_it_renders_the_correct_button_styles(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

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
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(fn () => 'Closure called');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertSame('eloquent-tables::action.row-action', $view->name());
        $this->assertArrayHasKey('action', $view->getData());
        $this->assertSame('Closure called', $view->getData()['action']);
    }

    public function test_it_builds_tooltip_correctly(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(action: '#', tooltip: 'tooltip');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertArrayHasKey('tooltip', $view->getData());
        $this->assertSame('tooltip', $view->getData()['tooltip']);

        $rowAction2 = new RowAction(action: '#', tooltip: fn (Model $model) => 'via closure');

        $view2 = $builder->build($rowAction2, $request, new TestModel());

        $this->assertArrayHasKey('tooltip', $view2->getData());
        $this->assertSame('via closure', $view2->getData()['tooltip']);
    }

    public function test_it_renders_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(action: '#');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    public function test_it_renders_the_correct_data_namespace(): void
    {
        config()->set('eloquent-tables.data-namespace', 'data-ns');

        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(action: '#');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertArrayHasKey('dataNamespace', $view->getData());
        $this->assertSame('data-ns', $view->getData()['dataNamespace']);
    }

    public function test_it_renders_the_correct_label(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(action: '#')->label('This is a label');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertArrayHasKey('label', $view->getData());
        $this->assertSame('This is a label', $view->getData()['label']);
    }

    public function test_it_renders_the_correct_confirm(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(action: '#', confirm: 'Confirm this action');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertArrayHasKey('confirm', $view->getData());
        $this->assertSame('Confirm this action', $view->getData()['confirm']);

        $rowAction2 = new RowAction(action: '#', confirm: fn (Model $model) => 'Confirm via closure');

        $view2 = $builder->build($rowAction2, $request, new TestModel());

        $this->assertArrayHasKey('confirm', $view2->getData());
        $this->assertSame('Confirm via closure', $view2->getData()['confirm']);
    }

    public function test_it_renders_the_correct_confirm_value(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(action: '#', confirmValue: 'This value');

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertArrayHasKey('confirmValue', $view->getData());
        $this->assertSame('This value', $view->getData()['confirmValue']);
    }

    public function test_it_renders_the_correct_as_form_value(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(action: '#')->asForm();

        $view = $builder->build($rowAction, $request, new TestModel());

        $this->assertArrayHasKey('asForm', $view->getData());
        $this->assertTrue($view->getData()['asForm']);

        $rowAction2 = new RowAction(action: '#');

        $view2 = $builder->build($rowAction2, $request, new TestModel());

        $this->assertArrayHasKey('asForm', $view2->getData());
        $this->assertFalse($view2->getData()['asForm']);

        $rowAction3 = new RowAction(action: '#', asForm: false);

        $view3 = $builder->build($rowAction3, $request, new TestModel());

        $this->assertArrayHasKey('asForm', $view3->getData());
        $this->assertFalse($view3->getData()['asForm']);
    }

    public function test_it_renders_the_correct_method(): void
    {
        /** @var RowActionViewBuilder $builder */
        $builder = $this->app->make(RowActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $rowAction = new RowAction(action: '#')->method(Method::Patch);
        $view      = $builder->build($rowAction, $request, new TestModel());

        $this->assertArrayHasKey('method', $view->getData());
        $this->assertSame(Method::Patch, $view->getData()['method']);

        $rowAction2 = new RowAction(action: '#', method: Method::Put);
        $view2      = $builder->build($rowAction2, $request, new TestModel());

        $this->assertArrayHasKey('method', $view2->getData());
        $this->assertSame(Method::Put, $view2->getData()['method']);

        $rowAction3 = new RowAction(action: '#')->get();
        $view3      = $builder->build($rowAction3, $request, new TestModel());

        $this->assertArrayHasKey('method', $view3->getData());
        $this->assertSame(Method::Get, $view3->getData()['method']);
        $this->assertTrue($view3->getData()['asForm']);

        $rowAction4 = new RowAction(action: '#')->post();
        $view4      = $builder->build($rowAction4, $request, new TestModel());

        $this->assertArrayHasKey('method', $view4->getData());
        $this->assertSame(Method::Post, $view4->getData()['method']);
        $this->assertTrue($view4->getData()['asForm']);

        $rowAction5 = new RowAction(action: '#')->put();
        $view5      = $builder->build($rowAction5, $request, new TestModel());

        $this->assertArrayHasKey('method', $view5->getData());
        $this->assertSame(Method::Put, $view5->getData()['method']);
        $this->assertTrue($view5->getData()['asForm']);

        $rowAction6 = new RowAction(action: '#')->patch();
        $view6      = $builder->build($rowAction6, $request, new TestModel());

        $this->assertArrayHasKey('method', $view6->getData());
        $this->assertSame(Method::Patch, $view6->getData()['method']);
        $this->assertTrue($view6->getData()['asForm']);

        $rowAction7 = new RowAction(action: '#')->delete();
        $view7      = $builder->build($rowAction7, $request, new TestModel());

        $this->assertArrayHasKey('method', $view7->getData());
        $this->assertSame(Method::Delete, $view7->getData()['method']);
        $this->assertTrue($view7->getData()['asForm']);
    }
}
