<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\TableAction;
use BrickNPC\EloquentTables\Builders\TableActionViewBuilder;

/**
 * @internal
 */
#[CoversClass(TableActionViewBuilder::class)]
#[UsesClass(Action::class)]
#[UsesClass(TableAction::class)]
#[UsesClass(Config::class)]
#[UsesClass(ButtonStyle::class)]
class TableActionViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var TableActionViewBuilder $builder */
        $builder = $this->app->make(TableActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new TableAction('#');

        $view = $builder->build($action, $request);

        $this->assertSame('eloquent-tables::action.table-action', $view->name());
    }

    public function test_it_returns_null_when_authorization_fails(): void
    {
        /** @var TableActionViewBuilder $builder */
        $builder = $this->app->make(TableActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new TableAction('#')->authorize(fn (Request $request) => false);

        $view = $builder->build($action, $request);

        $this->assertNull($view);
    }

    public function test_it_sets_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var TableActionViewBuilder $builder */
        $builder = $this->app->make(TableActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new TableAction('#');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    public function test_it_sets_the_correct_action(): void
    {
        /** @var TableActionViewBuilder $builder */
        $builder = $this->app->make(TableActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new TableAction('action');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('action', $view->getData());
        $this->assertSame('action', $view->getData()['action']);
    }

    public function test_it_sets_the_correct_styles(): void
    {
        /** @var TableActionViewBuilder $builder */
        $builder = $this->app->make(TableActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new TableAction('#')->styles(ButtonStyle::Info, ButtonStyle::Danger);

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('styles', $view->getData());
        $this->assertSame('btn-info btn-danger', $view->getData()['styles']);
    }

    public function test_it_sets_the_correct_label(): void
    {
        /** @var TableActionViewBuilder $builder */
        $builder = $this->app->make(TableActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new TableAction('#')->label('Label');

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('label', $view->getData());
        $this->assertSame('Label', $view->getData()['label']);
    }

    public function test_it_sets_the_correct_as_modal(): void
    {
        /** @var TableActionViewBuilder $builder */
        $builder = $this->app->make(TableActionViewBuilder::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        $action = new TableAction('#')->asModal();

        $view = $builder->build($action, $request);

        $this->assertArrayHasKey('asModal', $view->getData());
        $this->assertTrue($view->getData()['asModal']);
    }
}
