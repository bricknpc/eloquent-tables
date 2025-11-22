---
sidebar_position: 3
---

# Column Types

:::warning[Experimental]

Column types are an experimental feature and are likely to change in the future. Documentation about them is therefore 
light.

:::

Some types of data are not suited to be displayed as text. For those types of data, you can use a different column 
type. You can change a column type be setting the `type` to a different value. The value should be one of the 
`BrickNPC\EloquentTables\Enums\ColumnType` enum values, and defaults to `text`.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\ColumnType;

new Column(name: 'active', type: ColumnType::Boolean);
// Or
new Column(name: 'active')->type(ColumnType::Boolean);
// Or
new Column(name: 'active')->boolean();
new Column(name: 'active')->checkbox();
```