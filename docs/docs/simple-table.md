---
sidebar_position: 3
---

# Simple Table

To create an Eloquent Table, all you need to do is create a class that extends `BrickNPC\EloquentTables\Table` and add two methods: `query()` and `columns()`.

```php
<?php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\Sort;
use Illuminate\Contracts\Database\Query\Builder;

/**
 * @extends Table<User>
 */
class UserTable extends Table
{
    public function query(): Builder
    {
        return User::query();
    }
    
    /**
     * @return Column<User>[]
     */
    public function columns(): array
    {
        return [
            new Column('name')
                ->label(__('Name'))
                ->sortable(default: Sort::Asc)
                ->searchable(),
            new Column('email')
                ->label(__('Email address'))
                ->sortable()
                ->searchable(),
        ];
    }
}
```

This will create a simple table displaying the name and email address of all users in your application. You can use 
this table in a couple of different ways.

### As table object

You can pass an object of this table to your view and display the object to render the table.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Tables\UserTable;
use Illuminate\Support\Contracts\View;

class UsersIndexController
{
    public function __invoke(UserTable $userTable): View
    {
        return \view('users.index', [
            'table' => $userTable,
            'alternative' => new UserTable(),
        ]);
    }
}
```

```bladehtml
<div>
    {{ $table }}
</div>
```

### As Controller

If the table object is the only thing you want to show on the page you can also use the table itself directly as a 
controller. You don't need to do anything different for that, except maybe define a layout on the table. Just register 
the table as a route in your routes file:

```php
<?php
// routes/web.php

Route::get('users', App\Tables\UserTable::class)->name('users.index');
```
