<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\MassAction;
use BrickNPC\EloquentTables\Builders\MassActionViewBuilder;

/**
 * @internal
 */
#[CoversClass(MassActionViewBuilder::class)]
#[UsesClass(Config::class)]
#[UsesClass(MassAction::class)]
#[UsesClass(ButtonStyle::class)]
class MassActionViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#');

        $view = $builder->build($action, $request);

        $this->assertSame('eloquent-tables::action.mass-action', $view->name());
    }

    public function test_it_returns_null_when_authorization_fails(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#')->authorize(fn (Request $request) => false);

        $view = $builder->build($action, $request);

        $this->assertNull($view);
    }

    public function test_it_sets_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    public function test_it_sets_the_correct_data_namespace(): void
    {
        config()->set('eloquent-tables.data-namespace', 'data-ns');

        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame('data-ns', $view->getData()['dataNamespace']);
    }

    public function test_it_sets_a_unique_id(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('id', $view->getData());
    }

    public function test_it_sets_the_correct_action(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('action');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('action', $view->getData());
        $this->assertSame('action', $view->getData()['action']);
    }

    public function test_it_sets_the_correct_styles(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#')->styles(ButtonStyle::Info, ButtonStyle::Danger);

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('styles', $view->getData());
        $this->assertSame('btn-info btn-danger', $view->getData()['styles']);
    }

    public function test_it_sets_the_correct_label(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#')->label('Label');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('label', $view->getData());
        $this->assertSame('Label', $view->getData()['label']);
    }

    public function test_it_sets_the_correct_method(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#')->delete();

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('method', $view->getData());
        $this->assertSame(Method::Delete, $view->getData()['method']);
    }

    public function test_it_sets_the_correct_confirm(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#')->confirm('Confirm text', 'Confirm value');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('confirm', $view->getData());
        $this->assertArrayHasKey('confirmValue', $view->getData());
        $this->assertSame('Confirm text', $view->getData()['confirm']);
        $this->assertSame('Confirm value', $view->getData()['confirmValue']);
    }

    public function test_it_sets_the_correct_tooltip(): void
    {
        /** @var MassActionViewBuilder $builder */
        $builder = $this->app->make(MassActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new MassAction('#')->tooltip('Tooltip text');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('tooltip', $view->getData());
        $this->assertSame('Tooltip text', $view->getData()['tooltip']);
    }
}
