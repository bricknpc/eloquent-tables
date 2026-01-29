---
sidebar_position: 4
---

# Action collections

To visually group actions of the same type together you can use an `ActionCollection`. An action collection has zero 
or more actions that are defined exactly the same way as without a collection and a type of collection.

To use an action collection simply return an `BrickNPC\EloquentTables\Actions\Collections\ActionCollection` object in 
any place where you can return an `BrickNPC\EloquentTables\Actions\Action` object or an array of `BrickNPC\EloquentTables\Actions\Action`
objects.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

class UserTable extends Table
{
    public function rowActions(): ActionCollection
    {
        return new ActionCollection([
            new Action(), // define action here
            new Action(), // define action here
            new Action(), // define action here
        ]);
    }
}
```

## Action collection type

There are three action collection types available. The type determines how the actions are rendered visually.

- Normal (default)
- Grouped
- Dropdown

To change the type of collection being used, you can either set the type parameter, or use one of the helper methods.
The default type (Normal) doesn't change anything visually but it groups the actions semantically.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

class UserTable extends Table
{
    public function rowActions(): ActionCollection
    {
        return new ActionCollection([
            new Action(), // define action here
            new Action(), // define action here
            new Action(), // define action here
        ], ActionCollectionType::Grouped); // This will group actions together
    }
    
    public function tableActions(): ActionCollection
    {
        return new ActionCollection()->dropdown(
            new Action(), // define action here
            new Action(), // define action here
            new Action(), // define action here
        ); // This will create a dropdown menu of the actions
    }
    
    public function massActions(): ActionCollection
    {
        return new ActionCollection([
            new Action(), // define action here
            new Action(), // define action here
            new Action(), // define action here
        ]); // This will visually render each action as if there was no grouping, but they are contained in a single div element
    }
}
```

## Label

An action collection can have a label. This is especially useful for dropdown collections, as all the actions are rendered 
in the dropdown list so the button or link that opens the dropdown list should have a label.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

class UserTable extends Table
{
    public function tableActions(): ActionCollection
    {
        return new ActionCollection()->dropdown()->label('Open dropdown');
    }
}
```

## Nesting

Action Collections can be nested, for instance of one of the buttons in a group should be a dropdown with more actions.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

class UserTable extends Table
{
    public function tableActions(): ActionCollection
    {
        return new ActionCollection()->group(
            new Action(),
            new ActionCollection()->dropdown(
                new Action(),
                new Action(),
            ),
            new Action(),
        );
    }
}
```