---
sidebar_position: 1
---

# Table Styling

Table styles control the visual appearance of your tables and columns. They apply directly to the generated HTML 
table markup and integrate with your chosen theme (currently only Bootstrap 5).

You can set table styles on tables, by implementing the `tableStyles` method, and on columns by setting the 
`styles` property.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\TableStyle;

class UserTable extends Table
{
    //... Other methods
    
    /**
     * @return TableStyle[]
     */
    public function tableStyles(): array
    {
        return [
            TableStyle::Bordered,
        ];
    }
}
```

See the [columns](../columns.md#styles) documentation for more details about styles on columns.
