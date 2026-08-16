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

## Multi-column sorting

Clicking a second sortable column adds it to the sort rather than replacing it. The sort is applied in the order the
columns were clicked, so clicking email and then name orders by email first.

The sort lives under the [table's name](table-names.md) in the query string:

```
?user[sort][email]=asc&user[sort][name]=desc
```

Cycling a column through ascending, descending and off removes it again. A sort is remembered between visits — see
[Saved preferences](preferences.md).

:::note
In 1.x the sort was applied in the order the columns were declared on the table, whatever order they were clicked in.
See [Upgrading](upgrading.md).
:::
