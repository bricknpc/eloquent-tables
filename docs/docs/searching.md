---
sidebar_position: 10
---

# Searching

To make a table searchable, you only need to mark at least one of the columns as searchable.

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
            new Column('name')->searchable(),
        ];
    }
}
```

This will automatically add a search input to the table. For more details, check the [Column documentation](columns.md#searchable).
