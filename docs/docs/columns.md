---
sidebar_position: 6
---

# Columns

The `columns` method defines what data is shown in the Table. The `columns` method must return an array of 
`BrickNPC\EloquentTables\Column` objects.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;

class UserTable extends Table
{
    public function columns(): array
    {
        return [
            new Column('name')
                ->label(__('Name'))
                ->sortable(default: Sort::Asc),
                ->searchable(),
        ];
    }
}
```

## Column options

Columns have a number of different options that can be set. These options define how the table looks and functions.

### Name

Every column must have a name. This name is used to auto-generate a label if none is given, to fetch the data from the 
Model and as the database column name to sort the table. You can add the name via the constructor.

```php
<?php

use BrickNPC\EloquentTables\Column;

new Column(name: 'name');
```

### Label

The label is shown in the header of the column. A label is optional, if no label is supplied, the name of the column in 
title-case format is used as the label. A label can be set through the constructor or via a fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Column;

new Column(name: 'name', label: 'Name');
// Or
new Column(name: 'name')->label('Name');
```

### Value using

By default, the Table will use the name of the Column to get the data from the Model by calling `$model->{$name}`. But 
if the value you want to display in the table does not come from that property on the Model, or you want to for instance 
combine multiple properties into one column, you can use the `valueUsing` option.

The `valueUsing` property expects a Closure that receives the Model being rendered, and must return a string or 
Stringable value that will be displayed in the Table.

The `valueUsing` can be set through the constructor or a fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Column;

new Column(name: 'name', valueUsing: fn(User $user) => $user->firstname . ' ' . $user->lastname);
// Or
new Column(name: 'name')->valueUsing(fn(User $user) => $user->firstname . ' ' . $user->lastname);
```

:::note[Tip]

If you want to use HTML, you can wrap your string in a `Illuminate\Support\HtmlString` object and return that.

:::

### Sortable

You can make the Column sortable by setting the `sortable` option to `true`.

The `sortable` option can be set through the constructor or a fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Column;

new Column(name: 'name', sortable: true);
// Or
new Column(name: 'name')->sortable();
```

When a column is sortable, the Table will sort by the name of the column, like `$query->orderBy($column->name, $request->sortDirection)`.

### Sort using

By default, the Table will be sorted by the name of the column. But if you want to sort by a different column on the 
Model or use a custom sorting algorithm, you can use the `sortUsing` option.

The `sortUsing` option expects a Closure that receives the current request, the query from the `query` method and the
requested sort direction for the column. The Closure must not return anything.

The `sortUsing` option can be set through the constructor or through the fluent `sortable` method.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use lluminate\Contracts\Database\Query\Builder;

new Column(
    name: 'name', 
    sortUsing: fn(Request $request, Builder $query, Sort $direction) => $query->orderBy('lastname', $direction->value), 
);
// Or
new Column(name: 'name')
    ->sortable(
        sortUsing: fn(Request $request, Builder $query, Sort $direction) => $query->orderBy('lastname', $direction->value), 
    );
```

### Default sort

If you want to set a default sort direction for a column, you can set the `defaultSort` option to either 
`BrickNPC\EloquentTables\Enums\Sort::Asc`, `BrickNPC\EloquentTables\Enums\Sort::Desc`, or a `Closure` that performs 
the sorting.

When using a closure, the closure receives the current request and the query from the `query` method and must not 
return anything.

:::info

If you've set a default sort direction for a column, the header of the column will not show that the Table is sorted by
that column because the user has not clicked on the column header yet to sort by that column.

:::

The `defaultSort` option can be set through the constructor or through the fluent `sortable` method.

```php
<?php

use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;

new Column(name: 'name', defaultSort: Sort::Asc);
// Or
new Column(name: 'name')->sortable(default: Sort::Asc);
```

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'name', defaultSort: fn(Request $request, Builder $query) => $query->orderBy('lastname', 'asc'));
// Or
new Column(name: 'name')->sortable(default: fn(Request $request, Builder $query) => $query->orderBy('lastname', 'asc'));
```

:::note

If you want to set a default sort for a column, but not have it sortable, set the `defaultSort` option in the 
constructor and don't set the `sortable` option to `true`.

:::

### Searchable

You can make the Column searchable by setting the `searchable` option to `true`. Marking at least one column as 
searchable will automatically add a searchbar to the top of the table.

The `searchable` option can be set through the constructor or through the fluent `searchable` method.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'name', searchable: true);
// Or
new Column(name: 'name')->searchable();
```

### Search using

When a column is searchable, the Table will search the query by the name of the column using the `LIKE` operator. But if 
you want to use a custom search algorithm, you can use the `searchUsing` option.

The `searchUsing` option expects a Closure that receives the current request, the query from the `query` method and the 
search string. The Closure must not return anything.

The `searchUsing` option can be set through the constructor or through the fluent `searchable` method.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(
    name: 'name', 
    searchable: true, 
    searchUsing: fn(Request $request, Builder $query, string $search) => $query->where('lastname', 'LIKE', "%{$search}%"), 
);
// Or
new Column(name: 'name')
    ->searchable(
        searchUsing: fn(Request $request, Builder $query, string $search) => $query->where('lastname', 'LIKE', "%{$search}%"), 
    );
```

### Formatter

If you have values that should always be formatted in a certain way, you can use a [Formatter](formatting) to do so. A 
formatter is either an object implementing the `BrickNPC\EloquentTables\Contracts\Formatter` interface, or a string 
containing the classname of a class that implements the `BrickNPC\EloquentTables\Contracts\Formatter` interface.

If you supply a classname string, the package will automatically instantiate the class for you using the DI container.

A formatter can be set through the constructor or through the fluent `format` method.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'name', formatter: App\Formatters\NameFormatter::class);
// Or
new Column(name: 'name')->format(new App\Formatters\NameFormatter());
```

The Eloquent Tables package comes with a number of built-in formatters that you can use out of the box. You can use 
them by supplying the classname of the formatter to the column or by using the helper functions on the column.

#### Date formatter

The date formatter formats dates using the `\IntlDateFormatter`. It uses the timezone settings from the 
app config: `config('app.timezone')`. It uses the current locale of the app for the locale: `app()->getLocale()`.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'email_verified_at')->date();
```

Also see the PHP documentation for the [`IntlDateFormatter`](https://www.php.net/manual/en/class.intldateformatter.php) class.

#### Datetime formatter

The datetime formatter formats dates using the `\IntlDateFormatter`. It uses the timezone settings from the
app config: `config('app.timezone')`. It uses the current locale of the app for the locale: `app()->getLocale()`.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'created_at')->dateTime();
```

Also see the PHP documentation for the [`IntlDateFormatter`](https://www.php.net/manual/en/class.intldateformatter.php) class.

#### Number formatter

The number formatter formats numbers using the `\NumberFormatter` with `\NumberFormatter::DECIMAL`. It uses default 
number of decimals from the app config: `config('app.decimals')`. It uses the current locale of the app for the locale: 
`app()->getLocale()`. You can also specify the number of decimals and the locale to use.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'posts_count')->number(decimals: 0, locale: 'nl-NL');
```

Also see the PHP documentation for the [`NumberFormatter`](https://www.php.net/manual/en/class.numberformatter.php) class.

#### Float formatter

The float formatter is an alias of the number formatter. The only difference is that by default, the float formatter 
uses two decimals while the number formatter uses zero decimals.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'posts_count')->float(decimals: 2, locale: 'nl-NL');
```

Also see the PHP documentation for the [`NumberFormatter`](https://www.php.net/manual/en/class.numberformatter.php) class.

#### Currency formatter

The currency formatter formats currencies using the `\NumberFormatter` with `\NumberFormatter::CURRENCY`. It uses 
default currency from the app config: `config('app.currency')`. It uses the current locale of the app for the locale:
`app()->getLocale()`. You can also specify the currency and the locale to use.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'amount_paid')->currency(currency: 'EUR', locale: 'nl-NL');
```

Also see the PHP documentation for the [`NumberFormatter`](https://www.php.net/manual/en/class.numberformatter.php) class.

### Column type

:::warning[Experimental]

The column type feature is experimental and might change in the future.

:::

By default, the Table will render all columns as text. There are, however, some values that don't make sense to render 
as text. For instance, a column that contains a boolean value should be rendered differently. Therefor you can set the 
`type` option on your Column. The value must be one of the `BrickNPC\EloquentTables\Enums\ColumnType` enum cases.

The `type` option can be set through the constructor or through the fluent `type` method, or through on of the helper 
methods.

Currently, the only supported column types are `BrickNPC\EloquentTables\Enums\ColumnType::Boolean`, 
`BrickNPC\EloquentTables\Enums\ColumnType::Checkbox` and `BrickNPC\EloquentTables\Enums\ColumnType::Text`.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\ColumnType;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'name', type: ColumnType::Boolean);
// Or
new Column(name: 'name')->type(ColumnType::Boolean);
// Or
new Column(name: 'name')->boolean();
// Or
new Column(name: 'name')->checkbox();
```

### Styles

To style a cell, you can use two types of styles: Table styles and Cell styles. Table styles add styling to the 
`td` element, while Cell styles are added to a `div` (or similar element) inside both the `th` and `td` element.

You can use the `styles` option for the Table styles. The `styles` option expects an array of 
`BrickNPC\EloquentTable\Enums\TableStyle` enum cases, and can be set through the constructor or through the fluent 
`styles` method.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\TableStyle;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'name', styles: [TableStyle::Active, TableStyle::Success]);
// Or
new Column(name: 'name')->styles(TableStyle::Active, TableStyle::Success);
```

Setting this option will do nothing to the header of the column but will render the cell of the column as such (assuming 
the Bootstrap 5 theme):

```html
<td class="table-active table-success">
    ...
</td>
```

### Cell styles

Cell styles are similar to Table styles, but they are added to the `div` element inside the `td` element. The `cellStyles` 
option expects an array of `BrickNPC\EloquentTable\Enums\CellStyle` enum cases, and can be set through the constructor 
or through the fluent `cellStyles` method.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\CellStyle;
use lluminate\Contracts\Database\Query\Builder;

new Column(name: 'name', cellStyles: [CellStyle::AlignRight]);
// Or
new Column(name: 'name')->cellStyles(CellStyle::AlignRight);
```

This will result in the text of both the `th` as well as the `td` element being right aligned.

## Dependency injection

The `columns` method supports dependency injection, meaning you can typehint any object in the `columns` method and the
Eloquent Tables package will inject that dependency into your method.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;

class UserTable extends Table
{
    public function columns(MyService $service, AnotherService $another): array
    {
        return [
            new Column('name')
                ->label(__('Name'))
                ->sortable(default: Sort::Asc),
                ->searchable(),
        ];
    }
}
```

## Route model binding

The `columns` method also supports Route Model Binding in the same way controller methods do. You can typehint any 
Laravel Model in your `columns` method, and as long as the name of the parameter is the same as the name of the route 
parameter, the Eloquent Tables package will load the model from the database and inject it into your `columns` method.

```php
<?php
// routes/web.php

//... Other Route definitions
Route::get('{team}/users', App\Tables\UserTable::class);
```

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\Team;
use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;

class UserTable extends Table
{
    public function columns(Team $team): array
    {
        return [
            new Column('name')
                ->label(__('Name'))
                ->sortable(default: Sort::Asc),
                ->searchable(),
        ];
    }
}
```

Navigating to `http://my-app.test/1/users` will automatically try to load the Team with ID 1 and inject it into the
`columns` method. If there is no team with the given key, a `404 model not found` exception is thrown just like for 
normal route model binding in Laravel.