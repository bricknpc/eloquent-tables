<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\Theme;
use Illuminate\Database\Eloquent\Builder;
use BrickNPC\EloquentTables\Aggregates\Sum;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\AccentStyle;
use BrickNPC\EloquentTables\Enums\StyleFamily;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use Symfony\Component\HttpFoundation\Response;
use BrickNPC\EloquentTables\Builders\RowsBuilder;
use BrickNPC\EloquentTables\Enums\AggregateScope;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\Tables\TableRenderer;
use BrickNPC\EloquentTables\Services\LayoutFinder;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Actions\ActionRenderer;
use BrickNPC\EloquentTables\Filters\FilterRenderer;
use BrickNPC\EloquentTables\ValueObjects\FooterRow;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Services\TableParameters;
use BrickNPC\EloquentTables\Services\RouteModelBinder;
use BrickNPC\EloquentTables\Services\TablePreferences;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Tests\Resources\TestTable;
use BrickNPC\EloquentTables\Concerns\IgnoresNullValues;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Columns\ColumnLabelRenderer;
use BrickNPC\EloquentTables\Columns\ColumnValueRenderer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Exceptions\MissingMethodException;
use BrickNPC\EloquentTables\Tests\Resources\ArchivedTestTable;
use BrickNPC\EloquentTables\Tests\Resources\TestTableAuthorisationFails;
use BrickNPC\EloquentTables\Tests\Resources\TestTableAuthorisationFailsCustomData;
use BrickNPC\EloquentTables\Tests\Resources\TestTableAuthorisationFailsCustomCallback;

/**
 * @internal
 */
#[CoversClass(Table::class)]
#[CoversClass(WithPagination::class)]
#[UsesClass(TableRenderer::class)]
#[UsesClass(ColumnLabelRenderer::class)]
#[UsesClass(ColumnValueRenderer::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(LayoutFinder::class)]
#[UsesClass(TableStyle::class)]
#[UsesClass(Config::class)]
#[UsesClass(TableParameters::class)]
#[UsesClass(TablePreferences::class)]
#[UsesClass(RowsBuilder::class)]
#[UsesClass(FilterRenderer::class)]
#[UsesClass(RouteModelBinder::class)]
#[UsesClass(MissingMethodException::class)]
#[UsesClass(Theme::class)]
#[UsesClass(ActionRenderer::class)]
#[UsesClass(ActionContext::class)]
#[UsesClass(StyleResolver::class)]
#[UsesClass(CellStyle::class)]
#[UsesClass(StyleTarget::class)]
#[UsesClass(StyleFamily::class)]
#[UsesClass(StyleSet::class)]
#[UsesClass(AccentStyle::class)]
#[UsesClass(Sum::class)]
#[UsesClass(FooterRow::class)]
#[UsesClass(AggregateScope::class)]
#[UsesClass(IgnoresNullValues::class)]
class TableTest extends TestCase
{
    public function test_a_table_declares_no_footer_rows_by_default(): void
    {
        $this->assertSame([], new TestTable()->footer());
    }

    public function test_a_table_may_declare_its_own_footer_rows(): void
    {
        $table = new class extends Table {
            public function footer(): array
            {
                return [new FooterRow(new Sum(), AggregateScope::Total, 'All rows')];
            }
        };

        $footer = $table->footer();

        $this->assertCount(1, $footer);
        $this->assertSame('All rows', $footer[0]->resolveLabel());
        $this->assertSame(AggregateScope::Total, $footer[0]->scope);
    }

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

    public function test_it_throws_exception_when_query_method_is_not_implemented(): void
    {
        $table = new class extends Table {};

        $this->expectException(MissingMethodException::class);

        $table->render();
    }

    public function test_it_throws_exception_when_columns_method_is_not_implemented(): void
    {
        $table = new class extends Table {
            public function query(): void {}
        };

        $this->expectException(MissingMethodException::class);

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

    public function test_table_name_defaults_to_the_class_name_without_the_table_suffix(): void
    {
        $this->assertSame('test', new TestTable()->name());
    }

    public function test_table_name_snake_cases_a_multi_word_class_name(): void
    {
        $this->assertSame('archived_test', new ArchivedTestTable()->name());
    }

    public function test_table_name_keeps_the_whole_class_name_when_it_does_not_end_in_table(): void
    {
        $this->assertSame(
            'test_table_authorisation_fails',
            new TestTableAuthorisationFails()->name(),
        );
    }

    public function test_table_name_falls_back_when_stripping_the_suffix_leaves_nothing(): void
    {
        // An anonymous table is named "<parent>@anonymous<NUL><file>:<line>$<hash>" by PHP, so the
        // derivation resolves it to the parent, Table, which is entirely suffix.
        $table = new class extends Table {};

        $this->assertSame('table', $table->name());
    }

    public function test_table_name_can_be_overridden(): void
    {
        $table = new class extends Table {
            public function name(): string
            {
                return 'archived_users';
            }
        };

        $this->assertSame('archived_users', $table->name());
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
        /** @var Table&WithPagination $table */
        $table = $this->getTableWithPagination();

        /** @var Request $request */
        $request = $this->app->make('request');

        $this->assertSame(15, $table->perPage($request));
    }

    public function test_table_with_pagination_returns_a_declared_per_page(): void
    {
        $table = new class extends Table {
            use WithPagination;

            protected int $perPage = 25;

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');

        $this->assertSame(25, $table->perPage($request));
    }

    public function test_a_non_positive_declared_per_page_falls_back_to_the_default(): void
    {
        $table = new class extends Table {
            use WithPagination;

            protected int $perPage = 0;

            public function query(): Builder
            {
                return TestModel::query();
            }

            public function columns(): array
            {
                return [];
            }
        };

        /** @var Request $request */
        $request = $this->app->make('request');

        $this->assertSame(15, $table->perPage($request));
    }

    public function test_table_with_pagination_returns_the_default_per_page_options(): void
    {
        /** @var Table&WithPagination $table */
        $table = $this->getTableWithPagination();

        $this->assertSame([10, 15, 25, 50, 100], $table->perPageOptions());
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
