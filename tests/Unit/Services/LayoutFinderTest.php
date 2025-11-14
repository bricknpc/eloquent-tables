<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Services;

use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Attributes\Layout;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;

/**
 * @internal
 */
#[CoversClass(LayoutFinder::class)]
#[CoversClass(Layout::class)]
class LayoutFinderTest extends TestCase
{
    public function test_table_without_layout_returns_null(): void
    {
        $table = new class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        /** @var LayoutFinder $service */
        $service = $this->app->make(LayoutFinder::class);

        $layout = $service->getLayout($table);

        $this->assertNull($layout);
    }

    public function test_table_with_layout_method_returns_layout(): void
    {
        $table = new class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }

            public function layout(): Layout
            {
                return new Layout('layout-via-method');
            }
        };

        /** @var LayoutFinder $service */
        $service = $this->app->make(LayoutFinder::class);

        $layout = $service->getLayout($table);

        $this->assertInstanceOf(Layout::class, $layout);
        $this->assertSame('layout-via-method', $layout->name);
    }

    public function test_table_with_layout_attribute_returns_layout(): void
    {
        $table = new #[Layout(name: 'layout-via-attribute')] class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        /** @var LayoutFinder $service */
        $service = $this->app->make(LayoutFinder::class);

        $layout = $service->getLayout($table);

        $this->assertInstanceOf(Layout::class, $layout);
        $this->assertSame('layout-via-attribute', $layout->name);
    }

    public function test_table_with_both_layout_method_and_attribute_returns_layout_from_method(): void
    {
        $table = new #[Layout(name: 'layout-via-attribute')] class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }

            public function layout(): Layout
            {
                return new Layout('layout-via-method');
            }
        };

        /** @var LayoutFinder $service */
        $service = $this->app->make(LayoutFinder::class);

        $layout = $service->getLayout($table);

        $this->assertInstanceOf(Layout::class, $layout);
        $this->assertSame('layout-via-method', $layout->name);
    }
}
