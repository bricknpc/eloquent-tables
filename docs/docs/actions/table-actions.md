---
sidebar_position: 1
---

# Table Actions

Table actions are actions that are related to a table, but don't directly affect a table. For instance, if you have a 
users overview table, you might want to add a 'Create User' button that links to the create user page. That is what 
table actions are for, they are buttons / links to related pages.

To add table actions, implemented the `tableActions` method on your table. This method must return an array of 
`BrickNPC\EloquentTables\Actions\TableAction` objects.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\TableAction;

class UserTable extends Table
{
    //... Other methods
    
    /**
     * @return TableAction[]
     */
    protected function tableActions(): array
    {
        return [
            new TableAction(
                action: route('users.create'),
                label: __('Create user'),
            ),
        ];
    }
}
```

## Options

Table actions have a couple of different options you can use.

### Action

Every table action must have an action. The action is the URL the action links to.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use BrickNPC\EloquentTables\Actions\TableAction;

new TableAction(action: route('users.create'));
```

### Label

The label is the content displayed on the button. A label can be anything that can be rendered in the view, such as a 
string, a Stringable object or a HtmlAble object.

The `label` property can be set through the constructor or the fluent setter.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use BrickNPC\EloquentTables\Actions\TableAction;

new TableAction(action: route('users.create'), label: __('Create new user'));
// Or
new TableAction(action: route('users.create'))->label(__('Create new user'));
```

### Styles

You can customize the styling of a table action by providing one or more `BrickNPC\EloquentTables\Enums\ButtonStyle` 
enum values. These styles determine how the action is visually represented in the UI (for example: primary, secondary, 
danger, etc.).

The `styles` property accepts an array of `BrickNPC\EloquentTables\Enums\ButtonStyle` values and can be set through the 
constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\TableAction;

new TableAction(
    action: route('users.create'),
    styles: [ButtonStyle::Primary]
);

// Or using the fluent setter
new TableAction(action: route('users.create'))
    ->styles(ButtonStyle::Primary);

```

### Tooltip

A tooltip can be added to any table action. This tooltip will be shown when the user hovers over the action in the UI.

You can set the `tooltip` either in the constructor or via the fluent `tooltip()` method.

```php
<?php

use BrickNPC\EloquentTables\Actions\TableAction;

new TableAction(
    action: route('users.create'),
    tooltip: __('Create a new user')
);

// Or using the fluent setter
new TableAction(action: route('users.create'))
    ->tooltip(__('Create a new user'));
```

### As modal

:::warning[Not yet implemented]

This feature is not yet implemented.

:::

Sometimes you may want an action to open in a modal instead of navigating directly to another page. By enabling the 
`asModal` option, the action defined on the Table Action will be used to open a modal.

Enable this by passing `asModal: true` into the constructor, or by using the fluent `asModal()` method.

```php
<?php

use BrickNPC\EloquentTables\Actions\TableAction;

new TableAction(
    action: route('users.create'),
    asModal: true
);

// Or using the fluent setter
new TableAction(action: route('users.create'))
    ->asModal();
```

## Dependency injection

The `tableActions` method supports dependency injection, meaning you can typehint any object in the `tableActions` 
method and the Eloquent Tables package will inject that dependency into your method.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\TableAction;

class UserTable extends Table
{
    public function tableActions(MyService $service, AnotherService $another): array
    {
        return [
            //... Table action definitions
        ];
    }
}
```

## Route model binding

The `tableActions` method also supports Route Model Binding in the same way controller methods do. You can typehint any
Laravel Model in your `tableActions` method, and as long as the name of the parameter is the same as the name of the route
parameter, the Eloquent Tables package will load the model from the database and inject it into your `tableActions` method.

```php
<?php
// routes/web.php

//... Other Route definitions
Route::get('{team}/users', App\Tables\UserTable::class);
```

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\Team;
use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\TableAction;

class UserTable extends Table
{
    public function tableActions(Team $team): array
    {
        return [
            //... Table action definitions
        ];
    }
}
```

Navigating to `http://my-app.test/1/users` will automatically try to load the Team with ID 1 and inject it into the
`tableActions` method. If there is no team with the given key, a `404 model not found` exception is thrown just like for
normal route model binding in Laravel.