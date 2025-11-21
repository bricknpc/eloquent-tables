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

### Page name

The name of the current page query parameter can be set by adding a `pageName` property to your Table. If not set, the 
default value is `page`.

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
    
    protected string $pageName = 'users-page';
    
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

### Per page name

If you choose to be able to choose the number of items per page, you can customise the name of the query 
parameter that will be used to store the number of items per page by adding a `perPageName` property to your Table. By 
default, the name is `per_page`.

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
    
    protected string $perPageName = 'number-of-items-per-page';
    
    //... Table definition
}
```