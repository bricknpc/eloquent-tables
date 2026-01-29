---
sidebar_position: 15
---

# Dependency Injection

While creating tables, it can be useful to be able to use dependency injection or route model binding to build your 
Table. The Eloquent Tables package already injects a number of dependencies into your Table and supports 
dependency injection and route model binding on most methods.

## Default dependencies

By default, you have access to the following dependencies in your Table:

| Dependency                                          | Usage            | Comment                                                                               |
|-----------------------------------------------------|------------------|---------------------------------------------------------------------------------------|
| `Illuminate\Http\Request`                           | `$this->request` |                                                                                       |
| `Illuminate\Contracts\Translation\Translator`       | `$this->trans`   |                                                                                       |
| `Psr\Log\LoggerInterface`                           | `$this->logger`  |                                                                                       |
| `BrickNPC\EloquentTables\Builders\TableViewBuilder` | `$this->builder` | This should be treated as private for your Table, though technically it is available. |

Other dependencies can be injected into your Table by typehinting them in your method signature.

## Supported methods

The following methods support dependency injection:

- `columns()`
- `query()`
- `filters()`
- `tableActions()`
- `bulkActions()`
- `rowActions()`
- `layout()`

## Route model binding

The Eloquent Tables package supports route model binding on all methods that support dependency injection. The route 
model binding works the same as it does for Laravel Controllers. You can typehint any Eloquent Model on your method. As 
long as the name of the parameter matches the name of the route parameter, the Eloquent Tables package will 
automatically try to load the model from the database before injecting it into your method. This also works for 
named route model binding, where you use a different key for the route parameter value.

**Normal route model binding:**

```php
<?php
// routes/web.php

Route::get('team/{team}/users', App\Tables\UserTable::class)->name('teams.users');
```

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;

class UserTable extends Table
{
    //... Other methods
    
    protected function columns(Team $team): array
    {
        return [
            //... Column definitions
        ];
    }
}
```

**Route model binding using a different key:**

```php
<?php
// routes/web.php

Route::get('team/{team:uuid}/users', App\Tables\UserTable::class)->name('teams.users');
```

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;

class UserTable extends Table
{
    //... Other methods
    
    protected function columns(Team $team): array
    {
        return [
            //... Column definitions
        ];
    }
}
```