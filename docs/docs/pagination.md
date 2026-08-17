---
sidebar_position: 13
---

# Pagination

You can add pagination to your Table by using the `BrickNPC\EloquentTables\Concerns\WithPagination` trait.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Concerns\WithPagination;

class UserTable extends Table
{
    use WithPagination;
    
    //... Table definition
}
```

## Options

Pagination uses the default Laravel pagination options, but you can customise them.

### Items per page

The number of items per page can be set by adding a `perPage` property to your Table. If not set, the default value is `15`.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Concerns\WithPagination;

class UserTable extends Table
{
    use WithPagination;
    
    protected int $perPage = 10;
    
    //... Table definition
}
```

### Per page options

If you want users of your Table to be able to choose the number of items per page, you can add a `perPageOptions` 
property to your Table. This property should be an array of integers. By default, the options are 
`[10, 15, 25, 50, 100]`. If you want to disable this option and always use the `perPage` value, set the property to `[]`.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Concerns\WithPagination;

class UserTable extends Table
{
    use WithPagination;
    
    protected array $perPageOptions = [11, 22, 33, 55, 110];
    
    //... Table definition
}
```

### Query parameter names

The page and per-page parameters are nested under the [table's name](table-names.md), so `?user[page]=2` and
`?user[per_page]=50`. The sub-key names come from config rather than from the table:

```php
// config/eloquent-tables.php
'pagination' => [
    'page_query_name'     => 'page',
    'per_page_query_name' => 'per_page',
],
```

:::note
In 1.x these were the `pageName` and `perPageName` properties on the table. They were removed in 2.0, because the table name
already keeps one table's parameters apart from another's. See [Upgrading](upgrading.md).
:::

### Remembering the choice

A visitor's per-page choice is remembered and applied on their next visit. See [Saved preferences](preferences.md).
