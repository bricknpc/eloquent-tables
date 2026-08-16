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

## The query parameter

The search term is nested under the [table's name](table-names.md), so `?user[search]=ada`. Two tables on one page
therefore search independently. The sub-key comes from config:

```php
// config/eloquent-tables.php
'search' => [
    'query_name' => 'search',
],
```

Submitting the search keeps the table's sort, filters and per-page value, and returns it to the first page.

:::note
In 1.x the parameter was a page-wide `?search=`, shared by every table on the page. See [Upgrading](upgrading.md).
:::

