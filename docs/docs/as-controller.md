---
sidebar_position: 14
---

# Eloquent Table as Controller

You can use any Eloquent Table directly as a controller. You don't even need to create a Blade file for it. Create 
your Eloquent Table as normal and register it as a `GET` route.

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
    //... Configuration
}
```

```php
<?php
// routes/web.php

Route::get('/users', App\Tables\UserTable::class);
```

Your table is now available at `/users`.

## Layout

To render the Table inside your own layout file, you can configure the table to use a custom layout. There are two 
ways to do this, via an attribute or a method.

### Attribute

If you just want to use a custom layout for all tables, you can set the `Layout` attribute on the `Table` class. You can 
also set which section the table should be rendered in (defaults to `slot` in case your layout is a component), and you 
can also add any additional data to the layout via the `with` property.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Attributes\Layout;

#[Layout(name: 'layouts.app', section: 'content', with: ['foo' => 'bar'])]
class UserTable extends Table
{
    //... Configuration
}
```

### Method

The layout method must return the same Layout attribute as the attribute above. The only difference is that you have 
some more flexibility in defining the layout, as you are inside a method and therefore can use code to populate the 
`with` array for instance.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Attributes\Layout;

class UserTable extends Table
{
    //... Configuration
    
    public function layout(): Layout
    {
        return new Layout(
            name: 'layouts.app', 
            section: 'content', 
            with: ['foo' => 'bar'], 
        );
    }
}
```

## Dependency injection

When using the layout method to define your layout, you can also inject any dependencies you need into your layout 
method. The method supports [dependency injection](dependency-injection.md) and route model binding, same as the 
`query` and `columns` methods.