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

Columns have a number of different options that can be defined. These options define how the table looks and functions.

### Name

Every column must have a name. This name is used to auto-generate a label if none is given, to fetch the data from the 
Model and as the database column name to sort the table. You can add the name via the constructor.

```php
<?php

use BrickNPC\EloquentTables\Column;

new Column(name: 'name');
```

### Label

The label determines what the column is named in the header. A label is optional, if no label is supplied, the name 
value in title case is used as the label. A label can be set through the constructor or via a fluent setter.

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

> Tip:
> If you want to use HTML you can wrap your string in a HtmlString object and return that.

### Sortable

### Sort using

### Default sort

### Searchable

### Search using

### Formatter

### Column type

### Styles

### Cell styles

## Dependency injection

The `column` method supports dependency injection, meaning you can typehint any object in the `columns` method and the
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