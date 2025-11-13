# Styling

While Eloquent Tables uses frontend frameworks to determine base styling, there are variations available within those 
frameworks. Bootstrap, for instance, has a number of predefined classes that can be used to style tables.  
To set these configured options, you can use the `TableStyle` enum on both Table's and Column's.

> Note: The `TableStyle` enum uses the Bootstrap naming convention. When other frameworks are added, they will use 
> the same naming convention, but it will be translated to the framework's styles.

## Table Example

```php
<?php
// app/Tables/UsersTable.php

namespace App\Tables;

use BrickNPC\EloquentTables\Table
use BrickNPC\EloquentTables\Enums\TableStyle;

class UsersTable extends Table
{
    //... Other methods
    
    /**
     * @return TableStyle[]
     */
    public function tableStyles(): array
    {
        return [
            TableStyle::Striped,
            TableStyle::Hover,
            TableStyle::Success
        ];
    }
}
```

### Custom Example

```php
<?php
// app/Tables/UsersTable.php

namespace App\Tables;

use BrickNPC\EloquentTables\Table
use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\TableStyle;

class UsersTable extends Table
{
    //... Other methods
    
    /**
     * @return Column[]
     */
    public function columns(): array
    {
        return [
            new Column('name')->styles(TableStyle::Dark, TableStyle::StripedColumns),
        ];
    }
}
```