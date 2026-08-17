---
sidebar_position: 9
---

# Footers

A table that lists amounts invites the question "what does that come to". A footer answers it, one aggregate per column.

A footer is built from rows you declare. Each row renders one aggregate at one scope, across the columns that opted into
that aggregate.

```php
<?php
// app/Tables/InvoiceTable.php
declare(strict_types=1);

namespace App\Tables;

use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Aggregates\Sum;
use BrickNPC\EloquentTables\Enums\AggregateScope;
use BrickNPC\EloquentTables\ValueObjects\FooterRow;

class InvoiceTable extends Table
{
    public function columns(): array
    {
        return [
            new Column('number'),
            new Column('total')->currency()->aggregate(new Sum()),
        ];
    }

    public function footer(): array
    {
        return [
            new FooterRow(new Sum(), AggregateScope::Page, __('This page')),
            new FooterRow(new Sum(), AggregateScope::Total, __('All invoices')),
        ];
    }
}
```

That renders two footer rows: the total of the invoices on screen, and the total of every invoice matching the current
search and filters.

A table that declares no footer rows renders no footer at all.

## A column opts in

A footer row only fills a cell where the column declared that aggregate. Nothing is aggregated by accident, so an id, a
year or a postcode stays empty unless you ask for it.

```php
new Column('total')->aggregate(new Sum(), new Average());
```

Calling `aggregate()` again adds to what is already there rather than replacing it, the same way `style()` does.

A footer row naming an aggregate that no column offers renders as a row of empty cells.

## The two scopes

`AggregateScope::Page` aggregates the rows on screen. `AggregateScope::Total` aggregates everything matching the current
search and filters, ignoring the page limit.

On a paginated table these are different numbers, and which one a reader wants depends on what they are doing. Declare
both if both are useful.

:::note

A total-scope row costs an extra query per column it fills. Three columns aggregated across the whole set is three more
queries per page load. A page-scoped row costs nothing extra, because the rows are already loaded.

:::

## Available aggregates

| Aggregate | Current page | Whole result set | Renders in the column's unit |
|-----------|--------------|------------------|------------------------------|
| `Sum`     | Yes          | Yes              | Yes                          |
| `Average` | Yes          | Yes              | Yes                          |
| `Median`  | Yes          | No               | Yes                          |
| `Count`   | Yes          | Yes              | No                           |
| `Min`     | Yes          | Yes              | Yes                          |
| `Max`     | Yes          | Yes              | Yes                          |

They live in `BrickNPC\EloquentTables\Aggregates`.

`Median` has no whole-result-set answer because there is no median function that works across the databases Laravel
supports. Computing one would mean loading every matching row into memory, which defeats the point of paginating, so a
total-scope median renders as an empty cell instead.

## An empty result set

Each aggregate answers for itself rather than the footer guessing. `Sum` and `Count` return zero, because the sum and
count of nothing genuinely are zero. `Average`, `Median`, `Min` and `Max` render empty, because an empty set has no
average and no smallest value.

## Columns with no grand total

A column whose value comes from a closure has no database column behind it:

```php
new Column('line_total', valueUsing: fn (Invoice $invoice) => $invoice->quantity * $invoice->price)
    ->aggregate(new Sum());
```

A page-scoped row works, because the values are already computed. A total-scope row renders empty, because there is
nothing in the database to aggregate.

## Formatting

An aggregate that returns the column's unit renders through the column's formatter, so the sum of a currency column
renders as currency. A `Count` does not, because a count of a money column is a number of rows rather than an amount.

One exception. If a column's formatter takes a closure parameter, that closure resolves against a row, and a footer
value has no row:

```php
new Column('total')->currency(currency: fn (Invoice $invoice) => $invoice->currency);
```

The footer renders that value unformatted rather than refusing to aggregate it.

## The label

By default a row's label sits in a cell spanning the columns to the left of the aggregated ones. Every row spans the
same width, decided by the leftmost column any row in the footer aggregates, so stacked figures stay comparable.

To put a label somewhere else, name the column it should sit in:

```php
new FooterRow(new Sum(), AggregateScope::Total, __('All invoices'), labelColumn: 'number');
```

A label may also be a closure, which is resolved when the footer renders:

```php
new FooterRow(new Sum(), AggregateScope::Page, fn () => __('This page'));
```

## Styling a footer row

A footer row takes `RowStyle` cases, the same vocabulary [row styling](styling/table-styling.md) uses:

```php
use BrickNPC\EloquentTables\Enums\RowStyle;

new FooterRow(new Sum(), AggregateScope::Total, __('All invoices'), styles: [RowStyle::Primary]);
```

There is no closure form here, because a footer row has no model to vary on.

## Writing your own aggregate

An aggregate is a class satisfying `BrickNPC\EloquentTables\Contracts\Aggregate`. Nothing about the built-in ones is
special, so your own sits alongside them.

```php
<?php
// app/Tables/Aggregates/Range.php
declare(strict_types=1);

namespace App\Tables\Aggregates;

use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Contracts\Aggregate;
use Illuminate\Contracts\Database\Query\Builder;

final readonly class Range implements Aggregate
{
    public function forPage(Collection $values): mixed
    {
        return $values->max() - $values->min();
    }

    public function forQuery(Builder $query, string $column): mixed
    {
        return $query->max($column) - $query->min($column);
    }

    public function carriesColumnUnit(): bool
    {
        return true;
    }
}
```

Three things to know:

- **Return null for a scope you cannot answer.** That cell renders empty. This is how `Median` declines the whole
  result set, and it is how you decline a scope that would be too expensive or has no SQL form.
- **`forQuery()` receives the query with search, filters and sorting already applied**, and a fresh copy each time, so
  you can run whatever you need on it without affecting the rows or another aggregate.
- **The column's instance does the work.** A footer row names which aggregate it wants by class, and the instance the
  column declared computes it. So a column can configure its own aggregate while a row stays generic:

```php
// The column's rounding wins, and the footer row just asks for a Range.
new Column('total')->aggregate(new Range(precision: 2));

new FooterRow(new Range(), AggregateScope::Page, __('Spread'));
```
