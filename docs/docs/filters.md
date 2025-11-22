---
sidebar_position: 12
---

# Filters

You can add filters to your Table by implementing the `filters` method. This method should return an array of filter 
objects. A filter object must implement the `BrickNPC\EloquentTables\Contracts\Filter` interface.

## Default filter

The Eloquent Tables package comes with a default filter that allows you to filter your table by a single column. You 
can add a default filter by returning a `BrickNPC\EloquentTables\Filters\Filter` object from the `filters` method.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Filters\Filter;
use BrickNPC\EloquentTables\Contracts\Filter as FilterContract;

class UserTable extends Table
{
    //... Other methods
    
    /**
     * @return FilterContract[]
     */
    public function filters(): array
    {
        return [
            new Filter('status', StatusEnum::cases());
        ];
    }
}
```

Adding the filter from the example above will add a dropdown list to the top of the table, where each option is one of 
the values from the `StatusEnum` enum. Selecting one of those options will filter the table by that status with a simple 
`$query->where('status', '=', $value)`.

### Filter options

#### Name

Every filter must have a name. The name of the filter is used in the URL query string to filter the table, and it is 
also used as the database column to filter on.

```php
<?php

use BrickNPC\EloquentTables\Filters\Filter;

new Filter(name: 'status', options: []);
```

#### Options

Every filter must have options. Options are used to populate the dropdown list in the table.

```php
<?php

use BrickNPC\EloquentTables\Filters\Filter;

new Filter(name: 'status', options: StatusEnum::cases());
```

The options can be either a key-value array, a collection of key-values, a collection of models, or the cases of an 
enum.

#### Option key

The option key is an optional parameter that can be used to use a different value in the dropdown list than the 
key value stored in the database when supplying a collection or array of models to the `options` parameter.

By default, `$model->getKey()` is used to get the key value from the model.

#### Option label

The option label is an optional parameter that can be used to use a different label in the dropdown list than the
key value stored in the database when supplying a collection or array of models to the `options` parameter.

By default, `$model->getKey()` is used to get the label from the model.

#### Filter

If you want to use a custom filter, but it should still be a regular dropdown list to display the filter, you can set 
the `filter` property on the Filter object. This must be a closure that does the actual filtering. The `filter` closure 
will receive the current request, the query builder returned by the `query()` method, and the value of the filter. The 
closure should not return anything.

```php
<?php

use BrickNPC\EloquentTables\Filters\Filter;

new Filter(name: 'status', options: StatusEnum::cases())
    ->filter(fn(Request $request, Builder $query, string $value) => $query->where('status', '!=', $value);
```

## Custom filters

You can create custom filters by implementing the `BrickNPC\EloquentTables\Contracts\Filter` interface. A custom 
filter must implement all methods from the interface.

If you want to use a custom filter, you will also need to create a blade view that renders the filter.

### Name and options

Just like a default filter, a custom filter must have a name and options. The name is still used in the URL query 
string to filter the table, and the options are passed to the view of your filter.

### View

The `view()` method must return the name of the view that should be used to render your filter. When rendering the view, 
it receives the following variables:

- `theme`: THe current theme of the package, one of the `BrickNPC\EloquentTables\Enums\Theme` enum cases.
- `name`: The name of the filter.
- `options`: The rendered options of the filter. This is always a key-value array.
- `value`: The current value of the filter.
- `action`: The current URL including the query string. This can be used to submit the filter form.
- `queryName`: The name of the query string parameter that contains the filter values. This should be used in the form 
submit action as an array, where the key is the name of the filter.

### Options

The `options` method of a filter should return a key-value array which is used to render the options of the filter. The 
`options` method should take into account the fact that the options can be a collection of models, an array of models, 
an array of key-value pairs, or enum cases.

### __invoke

The `__invoke()` method of a filter should be used to filter the query. It receives the current request, the query 
builder returned by the `query()` method, and the value of the filter.` This method should not return anything.

## Dependency injection

The `filters` method supports dependency injection, meaning you can typehint any object in the `filters` method and the
Eloquent Tables package will inject that dependency into your method.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;

class UserTable extends Table
{
    public function filters(MyService $service, AnotherService $another): array
    {
        return [
            //... Filter definitions
        ];
    }
}
```

## Route model binding

The `filters` method also supports Route Model Binding in the same way controller methods do. You can typehint any
Laravel Model in your `filters` method, and as long as the name of the parameter is the same as the name of the route
parameter, the Eloquent Tables package will load the model from the database and inject it into your `filters` method.

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

class UserTable extends Table
{
    public function filters(Team $team): array
    {
        return [
            //... Filter definitions
        ];
    }
}
```

Navigating to `http://my-app.test/1/users` will automatically try to load the Team with ID 1 and inject it into the
`layout` method. If there is no team with the given key, a `404 model not found` exception is thrown just like for
normal route model binding in Laravel.