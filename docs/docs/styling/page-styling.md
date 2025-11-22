---
sidebar_position: 5
---

# Page Styling

Eloquent Tables not only renders a table, but also a header with options above the table and optional pagination links 
underneath the table. These items also require styling, so in order to have them in a style that fits with the table, 
you can define the page style on your table for these.

The page style affects the search box, filter dropdowns, the dropdown where users can choose the number of items to 
show per page, and the pagination links.

You can define the page style by implementing the `pageStyle()` method on your table. This method must return a 
`BrickNPC\EloquentTables\Enums\PageStyle` enum case.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\PageStyle;

class UserTable extends Table
{
    //... Other methods
    
    public function pageStyle(): PageStyle
    {
        return [
            PageStyle::Primary,
        ];
    }
}
```
