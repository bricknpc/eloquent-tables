---
sidebar_position: 5
---

# Query

The `query` method is what defines which data is shown in the table. A Table can also define additional filters later, 
but the `query` method defines the base data that populates the table.

The simplest version simply returns the Builder from the `query` method on the Eloquent model you're building the table 
for.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use Illuminate\Contracts\Database\Query\Builder;

class UserTable extends Table
{
    public function query(): Builder
    {
        return User::query();
    }
}
```

You can make the query as complicated as you wish. You can also already add conditions to the query in the
`query` method. These conditions are then always executed before any filtering, searching or sorting takes place.

## Dependency injection

The `query` method supports dependency injection, meaning you can typehint any object in the `query` method and the 
Eloquent Tables package will inject that dependency into your method.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use Illuminate\Contracts\Database\Query\Builder;

class UserTable extends Table
{
    public function query(MyService $service, AnotherService $another): Builder
    {
        return User::query();
    }
}
```

## Route model binding

The `query` method also supports Route Model Binding in the same way controller methods do. You can typehint any Laravel 
Model in your `query` method, and as long as the name of the parameter is the same as the name of the route parameter, 
the Eloquent Tables package will load the model from the database and inject it into your `query` method.

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
use Illuminate\Contracts\Database\Query\Builder;

class UserTable extends Table
{
    public function query(Team $team): Builder
    {
        return User::query()->whereBelongsTo($team);
    }
}
```

Navigating to `http://my-app.test/1/users` will automatically try to load the Team with ID 1 and inject it into the 
`query` method. If there is no team with the given key, a `404 model not found` exception is thrown just like for normal 
route model binding in Laravel.
