---
sidebar_position: 1
---

# Intro

A table is meant to display information on the screen, but often you also want to be able to do something with that 
data. To accomplish this, you can add `Actions` to your table.

Eloquent Tables has three different types of actions you can add to your table:

- General table actions
- Bulk actions
- Row actions

## General table actions

A general table action is an action that by default is displayed at the top of table next to the search bar. This can 
of course be modified by modifying the view files if you wish.

General table actions are actions that don't affect any data in the table directly. The most simple example would be 
a button or a link that opens a page to add a new entry to the table. Let's say you have a table that lists all your 
users, a general table action could be a button that says 'Add new user' and opens the add user form.

You can add as many table actions as you want to a table.

You can add general table actions by defining a public method called `tableActions()` on your table. The `tableActions()` 
method may return different things depending on your needs. It can be an array of `BrickNPC\EloquentTables\Actions\Action`
objects, a single `BrickNPC\EloquentTables\Actions\Action` if you only want to define one action, an array of 
`BrickNPC\EloquentTables\Actions\Collections\ActionCollection` objects or a 
single `BrickNPC\EloquentTables\Actions\Collections\ActionCollection` object.

For more information on how to build an [Action](action-definition.md) or an [Action Collection](action-collections.md), 
see their respective documentations.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;

class UserTable extends Table
{
    public function tableActions(): array
    {
        return [
            new Action(), // Define the action here
        ];
    }
}
```

## Bulk actions

A bulk action is an action that can affect zero or more rows in the table at once. If you add at least one bulk 
action to your table, the table will automatically be rendered with an extra column at the start of the table containing 
select boxes to select the row.

Bulk action are by default rendered above the table on the right side. This can of course be modified by modifying the 
view files if you wish.

A simple example of a bulk action could be deleting multiple rows at once. A bulk action automatically grabs the keys 
of all selected rows (Eloquent Models) and adds them to the request as an array of keys with the `keys` name.

You can add as many bulk actions as you want to a table.

You can add bulk table actions by defining a public method called `bulkActions()` on your table. The `bulkActions()`
method may return different things depending on your needs. It can be an array of `BrickNPC\EloquentTables\Actions\Action`
objects, a single `BrickNPC\EloquentTables\Actions\Action` if you only want to define one action, an array of
`BrickNPC\EloquentTables\Actions\Collections\ActionCollection` objects or a
single `BrickNPC\EloquentTables\Actions\Collections\ActionCollection` object.

For more information on how to build an [Action](action-definition.md) or an [Action Collection](action-collections.md),
see their respective documentations.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;

class UserTable extends Table
{
    public function bulkActions(): array
    {
        return [
            new Action(), // Define the action here
        ];
    }
}
```

## Row actions

A row action is an action that affects exactly one row in the table. If you add at least one row action to your table, 
the table will automatically be rendered with an extra column at the end of the table containing the row actions.

A simple example of a row action could be an 'Edit model' button.

You can add as many row actions as you want to a table.

You can add bulk table actions by defining a public method called `rowActions()` on your table. The `rowActions()`
method may return different things depending on your needs. It can be an array of `BrickNPC\EloquentTables\Actions\Action`
objects, a single `BrickNPC\EloquentTables\Actions\Action` if you only want to define one action, an array of
`BrickNPC\EloquentTables\Actions\Collections\ActionCollection` objects or a
single `BrickNPC\EloquentTables\Actions\Collections\ActionCollection` object.

For more information on how to build an [Action](action-definition.md) or an [Action Collection](action-collections.md),
see their respective documentations.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;

class UserTable extends Table
{
    public function rowActions(): array
    {
        return [
            new Action(), // Define the action here
        ];
    }
}
```

## Dependency injection

All `XXXactions()` methods support dependency injection, meaning you can typehint any object in the `XXXactions()` 
methods and the Eloquent Tables package will inject that dependency into your method.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;

class UserTable extends Table
{
    public function tableActions(MyService $service, AnotherService $another): array
    {
        return [
            new Action(), // Define the action here
        ];
    }
}
```

## Route model binding

The `XXXactions()` methods also support Route Model Binding in the same way controller methods do. You can typehint any
Laravel Model in your `XXXactions()` methods, and as long as the name of the parameter is the same as the name of the route
parameter, the Eloquent Tables package will load the model from the database and inject it into your `XXXactions()` method.

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
use BrickNPC\EloquentTables\Actions\Action;

class UserTable extends Table
{
    public function rowActions(Team $team): array
    {
        return [
            new Action('name'), // Define the action here
        ];
    }
}
```

Navigating to `http://my-app.test/1/users` will automatically try to load the Team with ID 1 and inject it into the
`columns` method. If there is no team with the given key, a `404 model not found` exception is thrown just like for
normal route model binding in Laravel.