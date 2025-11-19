<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Database\Eloquent\Builder;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use BrickNPC\EloquentTables\Builders\RowsBuilder;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Builders\TableViewBuilder;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Tests\Resources\TestTable;
use BrickNPC\EloquentTables\Builders\FilterViewBuilder;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;
use BrickNPC\EloquentTables\Builders\RowActionViewBuilder;
use BrickNPC\EloquentTables\Builders\MassActionViewBuilder;
use BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder;
use BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder;
use BrickNPC\EloquentTables\Builders\TableActionViewBuilder;
use BrickNPC\EloquentTables\Tests\Resources\TestTableAuthorisationFails;
use BrickNPC\EloquentTables\Tests\Resources\TestTableAuthorisationFailsCustomData;
use BrickNPC\EloquentTables\Tests\Resources\TestTableAuthorisationFailsCustomCallback;

/**
 * @internal
 */
#[CoversClass(Table::class)]
#[CoversClass(WithPagination::class)]
#[UsesClass(TableViewBuilder::class)]
#[UsesClass(ColumnLabelViewBuilder::class)]
#[UsesClass(ColumnValueViewBuilder::class)]
#[UsesClass(TableActionViewBuilder::class)]
#[UsesClass(RowActionViewBuilder::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(LayoutFinder::class)]
#[UsesClass(TableStyle::class)]
#[UsesClass(Config::class)]
#[UsesClass(RowsBuilder::class)]
#[UsesClass(MassActionViewBuilder::class)]
#[UsesClass(FilterViewBuilder::class)]
class TableTest extends TestCase
{
    public function test_default_authorisation_always_renders_the_table(): void
    {
        /** @var TestTable $table */
        $table = $this->app->make(TestTable::class);

        $rendered = $table->render();
        $invoked  = $table();
        $toString = (string) $table;

        $this->assertSame($rendered->name(), $invoked->name());
        $this->assertIsString($toString);
    }

    public function test_exception_is_thrown_when_authorization_fails(): void
    {
        /** @var TestTableAuthorisationFails $table */
        $table = $this->app->make(TestTableAuthorisationFails::class);

        $this->expectException(HttpException::class);

        $table->render();
    }

    public function test_exception_with_custom_message_and_custom_code_is_thrown_when_authorization_fails(): void
    {
        /** @var TestTableAuthorisationFailsCustomData $table */
        $table = $this->app->make(TestTableAuthorisationFailsCustomData::class);

        try {
            $table->render();
        } catch (HttpException $e) {
            $this->assertSame('This is a custom message.', $e->getMessage());
            $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getStatusCode());
        }
    }

    public function test_authorisation_fail_with_custom_callback_throws_custom_exception(): void
    {
        /** @var TestTableAuthorisationFailsCustomCallback $table */
        $table = $this->app->make(TestTableAuthorisationFailsCustomCallback::class);

        $this->expectException(\RuntimeException::class);

        $table->render();
    }

    public function test_table_can_check_for_pagination(): void
    {
        $withPagination    = $this->getTableWithPagination();
        $withoutPagination = $this->getTableWithoutPagination();

        $this->assertTrue($withPagination->withPagination());
        $this->assertFalse($withoutPagination->withPagination());
    }

    public function test_table_with_pagination_returns_default_per_page(): void
    {
        $table = $this->getTableWithPagination();

        /** @var Request $request */
        $request = $this->app->make('request');

        $this->assertSame(15, $table->getPerPage($request));
    }

    public function test_table_with_pagination_returns_per_page_in_request(): void
    {
        $table = $this->getTableWithPagination();

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('per_page', 10);

        $this->assertSame(10, $table->getPerPage($request));
    }

    #[DataProvider('invalidPerPageValues')]
    public function test_table_with_pagination_returns_default_per_page_for_invalid_request(string $invalidValue): void
    {
        $table = $this->getTableWithPagination();

        /** @var Request $request */
        $request = $this->app->make('request');
        $request->query->set('per_page', $invalidValue);

        $this->assertSame(15, $table->getPerPage($request));
    }

    public static function invalidPerPageValues(): \Generator
    {
        yield [
            'true',
        ];

        yield [
            'false',
        ];

        yield [
            'string',
        ];

        yield [
            'array',
        ];
    }

    private function getTableWithPagination(): Table
    {
        return new class extends Table {
            use WithPagination;

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };
    }

    private function getTableWithoutPagination(): Table
    {
        return new class extends Table {
            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };
    }
}
