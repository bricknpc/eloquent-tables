<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Builders;

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Actions\TableAction;
use BrickNPC\EloquentTables\Builders\TableActionViewBuilder;

/**
 * @internal
 */
#[CoversClass(TableActionViewBuilder::class)]
#[UsesClass(Action::class)]
#[UsesClass(TableAction::class)]
#[UsesClass(Config::class)]
class TableActionViewBuilderTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var TableActionViewBuilder $builder */
        $builder = $this->app->make(TableActionViewBuilder::class);

        $action = new TableAction('#');

        $view = $builder->build($action);

        $this->assertSame('eloquent-tables::action.table-action', $view->name());
    }

    // todo: test each individual data item
}
