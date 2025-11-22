---
sidebar_position: 11
---

# Sorting

Table sorting is defined by the columns of a table. Mark a column as sortable, and it will automatically be 
sortable.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;

class UserTable extends Table
{
    //... Other methods
    
    protected function columns(): array
    {
        return [
            new Column('name')->sortable(),
        ];
    }
}
```

For more details, check the [Column documentation](columns.md#sortable).