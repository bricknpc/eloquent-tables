<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Tests\Unit\Columns;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\Theme;
use BrickNPC\EloquentTables\Tests\TestCase;
use PHPUnit\Framework\Attributes\UsesClass;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\ColumnType;
use BrickNPC\EloquentTables\Enums\TableStyle;
use PHPUnit\Framework\Attributes\CoversClass;
use BrickNPC\EloquentTables\Enums\StyleFamily;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use BrickNPC\EloquentTables\Enums\TableRegion;
use BrickNPC\EloquentTables\Columns\ColumnValue;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Tests\Resources\TestModel;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Formatters\NumberFormatter;
use BrickNPC\EloquentTables\Columns\ColumnValueRenderer;
use BrickNPC\EloquentTables\Styles\Contexts\CellContext;
use BrickNPC\EloquentTables\Formatters\CurrencyFormatter;
use BrickNPC\EloquentTables\Formatters\DateTimeFormatter;
use BrickNPC\EloquentTables\Tests\Resources\TestFormatter;

/**
 * @internal
 */
#[CoversClass(ColumnValueRenderer::class)]
#[UsesClass(FormatterFactory::class)]
#[UsesClass(Column::class)]
#[UsesClass(Config::class)]
#[UsesClass(ColumnType::class)]
#[UsesClass(TableStyle::class)]
#[UsesClass(CellStyle::class)]
#[UsesClass(Theme::class)]
#[UsesClass(CurrencyFormatter::class)]
#[UsesClass(NumberFormatter::class)]
#[UsesClass(DateTimeFormatter::class)]
#[UsesClass(StyleSet::class)]
#[UsesClass(CellContext::class)]
#[UsesClass(StyleTarget::class)]
#[UsesClass(StyleFamily::class)]
#[UsesClass(TableRegion::class)]
#[UsesClass(StyleResolver::class)]
#[UsesClass(ColumnValue::class)]
class ColumnValueRendererTest extends TestCase
{
    public function test_it_returns_the_correct_view(): void
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name');

        $view = $builder->build($request, $column, $model);

        $this->assertSame('eloquent-tables::table.td', $view->name());
    }

    public function test_it_builds_and_uses_formatter(): void
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->format(TestFormatter::class);

        $view = $builder->build($request, $column, $model);

        $this->assertSame('eloquent-tables::table.td', $view->name());
        $this->assertStringContainsString('formatted', $view->render());
    }

    public function test_it_uses_formatter(): void
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->format(new TestFormatter());

        $view = $builder->build($request, $column, $model);

        $this->assertSame('eloquent-tables::table.td', $view->name());
        $this->assertStringContainsString('formatted', $view->render());
    }

    public function test_it_renders_the_correct_theme(): void
    {
        config()->set('eloquent-tables.theme', Theme::Bootstrap5);

        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name');

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('theme', $view->getData());
        $this->assertSame(Theme::Bootstrap5, $view->getData()['theme']);
    }

    public function test_it_renders_the_correct_styles(): void
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->style(CellStyle::BackgroundSuccess, CellStyle::FontBold);

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('styles', $view->getData());
        $this->assertSame('table-success fw-bold', $view->getData()['styles']);
    }

    public function test_it_renders_the_correct_cell_styles(): void
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->style(CellStyle::AlignCenter, CellStyle::AlignBetween);

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('cellStyles', $view->getData());
        $this->assertSame('justify-content-center justify-content-between', $view->getData()['cellStyles']);
    }

    public function test_a_boolean_column_honours_a_declared_alignment(): void
    {
        // Covers AE2.
        $html = $this->renderCell(new Column('name')->boolean()->style(CellStyle::AlignRight));

        $this->assertStringContainsString('justify-content-end', $html);
        $this->assertStringNotContainsString('justify-content-center', $html);
    }

    public function test_a_checkbox_column_honours_a_declared_alignment(): void
    {
        // Covers AE2.
        $html = $this->renderCell(new Column('name')->checkbox()->style(CellStyle::AlignRight));

        $this->assertStringContainsString('justify-content-end', $html);
        $this->assertStringNotContainsString('justify-content-center', $html);
    }

    public function test_a_boolean_column_still_centres_by_default(): void
    {
        // Covers AE3.
        $this->assertStringContainsString('justify-content-center', $this->renderCell(new Column('name')->boolean()));
    }

    public function test_a_declared_alignment_displaces_the_type_default(): void
    {
        // Covers AE3.
        $html = $this->renderCell(new Column('name')->boolean()->style(CellStyle::AlignLeft));

        $this->assertStringContainsString('justify-content-start', $html);
        $this->assertStringNotContainsString('justify-content-center', $html);
    }

    public function test_a_declared_background_does_not_displace_the_type_default(): void
    {
        $html = $this->renderCell(new Column('name')->boolean()->style(CellStyle::BackgroundSuccess));

        $this->assertStringContainsString('justify-content-center', $html);
        $this->assertStringContainsString('table-success', $html);
    }

    public function test_a_closure_colours_only_the_rows_that_match(): void
    {
        // Covers AE4.
        $column = new Column('name')->style(
            CellStyle::AlignRight,
            static fn (CellContext $context) => $context->model?->name === 'negative'
                ? CellStyle::TextDanger
                : null,
        );

        $matching = $this->renderCell($column, 'negative');
        $other    = $this->renderCell($column, 'positive');

        $this->assertStringContainsString('text-danger', $matching);
        $this->assertStringContainsString('justify-content-end', $matching);
        $this->assertStringNotContainsString('text-danger', $other);
        $this->assertStringContainsString('justify-content-end', $other);
    }

    public function test_it_renders_the_correct_type(): void
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name')->boolean();

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('type', $view->getData());
        $this->assertSame(ColumnType::Boolean, $view->getData()['type']);
    }

    public function test_it_renders_the_correct_icons(): void
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');
        $model   = new class extends Model {};
        $column  = new Column('name');

        $view = $builder->build($request, $column, $model);

        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('checkIcon', $view->getData());
        $this->assertSame('✓', $view->getData()['checkIcon']);
        $this->assertArrayHasKey('crossIcon', $view->getData());
        $this->assertSame('✗', $view->getData()['crossIcon']);
    }

    public function test_a_closure_formatter_parameter_is_resolved_with_the_row_model(): void
    {
        $received = [];

        $column = new Column('amount')->currency(
            static function (Model $model) use (&$received): string {
                $received[] = $model;

                return $model->currency;
            },
            'en_US',
        );

        $dollars = $this->render($column, $this->modelWith(['amount' => 5, 'currency' => 'USD']));
        $yen     = $this->render($column, $this->modelWith(['amount' => 5, 'currency' => 'JPY']));

        $this->assertStringContainsString('$5.00', $dollars);
        $this->assertStringContainsString('¥5', $yen);

        // The closure is called once per row, with that row's model.
        $this->assertCount(2, $received);
        $this->assertSame('USD', $received[0]->currency);
        $this->assertSame('JPY', $received[1]->currency);
    }

    public function test_a_closure_can_supply_the_number_of_decimals(): void
    {
        $column = new Column('amount')->number(static fn (Model $model) => $model->decimals, 'en_US');

        $this->assertStringContainsString('1.500', $this->render($column, $this->modelWith(['amount' => 1.5, 'decimals' => 3])));
        $this->assertStringContainsString('2', $this->render($column, $this->modelWith(['amount' => 1.5, 'decimals' => 0])));
    }

    public function test_a_closure_can_supply_the_timezone_as_a_string(): void
    {
        $column = new Column('moment')->dateTime('en_US', static fn (Model $model) => $model->timezone);

        $moment = new \DateTimeImmutable('2026-01-01 11:00:00', new \DateTimeZone('UTC'));

        $utc   = $this->render($column, $this->modelWith(['moment' => $moment, 'timezone' => 'UTC']));
        $tokyo = $this->render($column, $this->modelWith(['moment' => $moment, 'timezone' => 'Asia/Tokyo']));

        // ICU puts a narrow no-break space before AM/PM, so assert on the time only.
        $this->assertStringContainsString('11:00', $utc);
        $this->assertStringContainsString('8:00', $tokyo);
        $this->assertStringNotContainsString('11:00', $tokyo);
    }

    public function test_parameters_that_are_not_closures_are_passed_through_unchanged(): void
    {
        $column = new Column('amount')->currency('GBP', 'en_GB');

        $this->assertStringContainsString('£5.00', $this->render($column, $this->modelWith(['amount' => 5])));
    }

    private function renderCell(Column $column, string $name = 'Ada'): string
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request     = $this->app->make('request');
        $model       = new TestModel();
        $model->name = $name;

        return $builder->build($request, $column, $model)->render();
    }

    private function modelWith(array $attributes): Model
    {
        $model = new class extends Model {
            protected $guarded = [];
        };

        return $model->forceFill($attributes);
    }

    private function render(Column $column, Model $model): string
    {
        /** @var ColumnValueRenderer $builder */
        $builder = $this->app->make(ColumnValueRenderer::class);

        /** @var Request $request */
        $request = $this->app->make('request');

        return $builder->build($request, $column, $model)->render();
    }
}
