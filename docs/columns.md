# Columns

Columns define what data is shown in the table. You can define columns in the `columns` method of your table. The 
`columns` method returns an array of `Column` objects.

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
    /**
    * @return Column[]
     */
    public function columns(): array
    {
        return [
            new Column('email'),
            new Column('name'),
        ];
    }
}
```

The name you enter for each column will be used to display the column in the table. The table will check if there is 
a property with that name on the model. 

If you have more complex logic for what data should be shown, you can provide a closure to the `valueUsing` method. 
This closure will receive the model that is currently being displayed, and should return either a string or a Stringable 
object with the content to be displayed.

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
    /**
    * @return Column[]
     */
    public function columns(): array
    {
        return [
            new Column('email'),
            new Column('name')->valueUsing(fn (User $user) => $user->firstname . ' ' . $user->lastname),
        ];
    }
}
```

## Sorting

You can add the ability to sort the table by a specific column by adding the `sortable` property to the column.
```php
<?php

/**
 * @return Column[]
 */
public function columns(): array
{
    return [
        new Column('email')->sortable(),
    ];
}
```

### Default Sorting

If you want the table to be sorted by a specific column by default, you can use the `defaultSort` option.

```php
<?php

/**
 * @return Column[]
 */
public function columns(): array
{
    return [
        new Column('email')->sortable(default: \BrickNPC\EloquentTables\Enums\Sort::Asc),
    ];
}
```

### Custom Sorting

By default, the sorting algorithm will just sort the table by the name of the column in the direction provided by the 
user. If you want to customize the sorting algorithm, you can provide a closure to the `sortUsing` option. This closure 
will receive the current request, the query provided by the `query()` method and the direction of sorting that is 
requested by the user. The closure should not return anything.

```php
<?php

/**
 * @return Column[]
 */
public function columns(): array
{
    return [
        new Column('email')->sortable(sortUsing: function(\Illuminate\Http\Request $request, \Illuminate\Contracts\Database\Query\Builder $query, \BrickNPC\EloquentTables\Enums\Sort $direction): void {
            // Sort the query here
            $query->orderBy('email', $direction->value);
        }),
    ];
}
```

> The sorting closure is only called when the user actually requests sorting of that column, therefore it can not be used 
> to sort the table by default. This functionality will be added in a future release.

## Searching

You can make a column searchable by adding setting the `searchable` option. If there are no columns that are searchable, 
the table will not be searchable and the search field will not be shown. If there is at least one searchable column, the 
search field will be shown.

```php
<?php

/**
 * @return Column[]
 */
public function columns(): array
{
    return [
        new Column('email')->searchable(),
    ];
}
```

If there are multiple columns that are searchable, each column will be added to the search query with an `OR` operator.

```php
<?php

/**
 * @return Column[]
 */
public function columns(): array
{
    return [
        new Column('email')->searchable(),
        new Column('name')->searchable(),
    ];
}

// This will result in the following search algorithm
$query->where(function (\Illuminate\Contracts\Database\Query\Builder $query) use ($searchValue) {
    $query
        ->whereLike('email', '%'.$searchValue.'%')
        ->orWhereLike('name', '%'.$searchValue.'%')
    ;
});

```

### Custom Searching

By default, the search algorithm will perform a simple `whereLike('name_of_the_column', '%'.$searchValue.'%')` on the 
query. If you want to customise the search algorithm, you can provide a closure to the `searchUsing` option. This closure 
will receive the current request, the query provided by the `query()` method and the search value entered by the user. 
The closure should not return anything.

```php
<?php

/**
 * @return Column[]
 */
public function columns(): array
{
    return [
        new Column('name')->searchable(searchUsing: function(\Illuminate\Http\Request $request, \Illuminate\Contracts\Database\Query\Builder $query, string $searchValue): void {
            // Search the query here
            $query->where(function (\Illuminate\Contracts\Database\Query\Builder $query) use ($searchValue) {
                $query
                    ->whereLike('firstname', '%'.$searchValue.'%')
                    ->orWhereLike('lastname', '%'.$searchValue.'%')
                ;
            });
        }),
    ];
}
```