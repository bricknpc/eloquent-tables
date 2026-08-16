---
sidebar_position: 1
---

# Table Styling

Table styles control the visual appearance of your tables and columns. They apply directly to the generated HTML 
table markup and integrate with your chosen theme (currently only Bootstrap 5).

You can set table styles by implementing the `style()` method on your table. It returns a `StyleSet`, the same shape
every other styling level uses.

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

## Styling a row

A row style covers the whole row, including the bulk-action checkbox and the row-action cells that column styles cannot
reach. Declare it with `rowStyle()`, over `BrickNPC\EloquentTables\Enums\RowStyle`:

```php
<?php

use BrickNPC\EloquentTables\Enums\RowStyle;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Styles\Contexts\RowContext;

public function rowStyle(): ?StyleSet
{
    return new StyleSet(
        fn (RowContext $context) => $context->model->is_overdue ? RowStyle::Danger : null,
    );
}
```

The closure receives a `RowContext` carrying that row's model. Returning `null` leaves the row unstyled.

:::note
You can approximate this by giving every column the same conditional style, but the leading checkbox cell and the
trailing actions cell sit outside the column loop, so they would stay uncoloured and the row would look broken at both
ends.
:::
