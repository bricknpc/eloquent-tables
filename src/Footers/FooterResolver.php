<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Footers;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Columns\ColumnValue;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use BrickNPC\EloquentTables\Contracts\Formatter;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\Enums\AggregateScope;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\ValueObjects\FooterRow;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooter;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooterRow;

/**
 * @template TModel of Model
 */
readonly class FooterResolver
{
    public function __construct(
        private FormatterFactory $formatterFactory,
        private StyleResolver $styleResolver,
        private ColumnValue $columnValue = new ColumnValue(),
    ) {}

    /**
     * @param FooterRow[]            $footerRows
     * @param array<Column<TModel>>  $columns
     * @param Collection<int, Model> $rows
     * @param int                    $leadingCells cells before the first column, such as the
     *                                             bulk action checkbox
     */
    public function resolve(
        array $footerRows,
        array $columns,
        Collection $rows,
        ?Builder $query,
        int $leadingCells = 0,
    ): ResolvedFooter {
        $columns = array_values($columns);

        $resolved = [];

        foreach ($footerRows as $footerRow) {
            $resolved[] = new ResolvedFooterRow(
                $footerRow->resolveLabel(),
                $this->valuesFor($footerRow, $columns, $rows, $query),
                $this->labelIndex($footerRow, $columns),
                $this->styleResolver->classes($footerRow->styles),
            );
        }

        $firstValueIndex = $this->firstAggregated($footerRows, $columns);

        return new ResolvedFooter($resolved, $leadingCells + $firstValueIndex, $firstValueIndex);
    }

    /**
     * @param array<int, Column<TModel>> $columns
     * @param Collection<int, Model>     $rows
     *
     * @return array<int, string>
     */
    private function valuesFor(FooterRow $footerRow, array $columns, Collection $rows, ?Builder $query): array
    {
        $values = [];

        foreach ($columns as $index => $column) {
            $aggregate = $this->offered($column, $footerRow->aggregate);

            $values[$index] = $aggregate === null
                ? ''
                : $this->render($column, $aggregate, $this->compute($aggregate, $footerRow->scope, $column, $rows, $query));
        }

        return $values;
    }

    /**
     * @param Column<TModel>         $column
     * @param Collection<int, Model> $rows
     */
    private function compute(
        Aggregate $aggregate,
        AggregateScope $scope,
        Column $column,
        Collection $rows,
        ?Builder $query,
    ): float|int|string|\Stringable|null {
        if ($scope === AggregateScope::Page) {
            return $aggregate->forPage($rows->map(
                // Column's TModel is invariant, so a Column<TModel> cannot be handed to a method
                // @mago-expect analysis:possibly-invalid-argument
                // that binds its template from the model argument.
                fn (Model $model) => $this->columnValue->resolve($column, $model), // @phpstan-ignore argument.type
            ));
        }

        // A column computed in PHP has no database column behind it. Handing its name to the query
        // aggregates nothing on Postgres and MySQL, and on SQLite silently sums a string literal to
        // zero, so the whole result set is declined instead.
        if ($query === null || $column->valueUsing !== null) {
            return null;
        }

        return $aggregate->forQuery($query, $column->name);
    }

    /**
     * @param Column<TModel> $column
     */
    private function render(Column $column, Aggregate $aggregate, float|int|string|\Stringable|null $value): string
    {
        if ($value === null) {
            return '';
        }

        if (!$aggregate->carriesColumnUnit() || $column->formatter === null) {
            return $this->toString($value);
        }

        // A closure formatter parameter resolves against a row, and a footer value has none, so the
        // value is rendered unformatted rather than refused.
        if (array_any($column->getFormatterParameters(), static fn (mixed $parameter) => $parameter instanceof \Closure)) {
            return $this->toString($value);
        }

        $formatter = $column->formatter instanceof Formatter
            ? $column->formatter
            : $this->formatterFactory->build($column->formatter, $column->getFormatterParameters());

        return (string) $formatter->format($value);
    }

    private function toString(float|int|string|\Stringable $value): string
    {
        return (string) $value;
    }

    /**
     * The column's own instance computes, so a column may configure its aggregate while a row names
     * only which aggregate it wants.
     *
     * @param Column<TModel> $column
     */
    private function offered(Column $column, Aggregate $wanted): ?Aggregate
    {
        foreach ($column->aggregates as $offered) {
            if ($offered::class === $wanted::class) {
                return $offered;
            }
        }

        return null;
    }

    /**
     * @param array<int, Column<TModel>> $columns
     */
    private function labelIndex(FooterRow $footerRow, array $columns): ?int
    {
        if ($footerRow->labelColumn === null) {
            return null;
        }

        foreach ($columns as $index => $column) {
            if ($column->name === $footerRow->labelColumn) {
                return $index;
            }
        }

        return null;
    }

    /**
     * The leftmost column any row aggregates, so every label ends at the same place.
     *
     * @param FooterRow[]                $footerRows
     * @param array<int, Column<TModel>> $columns
     */
    private function firstAggregated(array $footerRows, array $columns): int
    {
        foreach ($columns as $index => $column) {
            foreach ($footerRows as $footerRow) {
                if ($this->offered($column, $footerRow->aggregate) !== null) {
                    return $index;
                }
            }
        }

        return count($columns);
    }
}
