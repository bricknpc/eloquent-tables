---
sidebar_position: 3
---

# Mass Actions

Mass actions are actions that can impact multiple rows on the table. For instance, if you want a 'Delete All' option, 
you can add a Mass Action for this. Adding a mass action will add a button to execute the mass action to the table, 
and it will also a column to the start of the table with checkboxes, as well as a switch to select or deselect all 
checkboxes at once.

To add mass actions, implemented the `massActions` method on your table. This method must return an array of
`BrickNPC\EloquentTables\Actions\MassAction` objects.

## Options

Mass actions offer several configuration options that control their behavior, appearance, authorization, and confirmation flow.

### Action

Every mass action must have an action. This is the URL the selected models will be submitted to when the action is 
triggered.

```php
<?php

use BrickNPC\EloquentTables\Actions\MassAction;

new MassAction(action: route('users.mass-delete'));
```

### Label

The `label` is the content displayed on the mass action button. A label accepts anything renderable by the view, 
including strings, Stringable values, or Htmlable instances.

You can set the `label` using the constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Actions\MassAction;

new MassAction(action: route('users.export'), label: __('Export Users'));
// Or
new MassAction(action: route('users.export'))
    ->label(__('Export Users'));
```

### Styles

Mass actions support the same styling system as table actions. 

You may pass an array of `BrickNPC\EloquentTables\Enums\ButtonStyle` enum values to control the visual appearance of the 
action.

You can set the `styles` using the constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\MassAction;

new MassAction(
    action: route('users.export'),
    styles: [ButtonStyle::Danger]
);
// Or
new MassAction(action: route('users.export'))
    ->styles(ButtonStyle::Danger);
```

### Method

Mass Actions perform form submissions, and you can control which HTTP method is used. By default, the method 
is `POST`, but you can change it using either the `method()` setter or one of the dedicated convenience methods.

```php
<?php

use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\MassAction;

// Using the constructor
new MassAction(action: route('users.mass-delete'), method: Method::Delete);

// Using the method() setter
new MassAction(action: route('users.mass-delete'))
    ->method(Method::Delete);

// Using convenience methods
new MassAction(action: route('users.export'))->get();
new MassAction(action: route('users.export'))->post();
new MassAction(action: route('users.mass-update'))->put();
new MassAction(action: route('users.export'))->patch();
new MassAction(action: route('users.export'))->delete();
```

### Authorization

Mass actions may define an authorization callback that determines whether the current user is allowed to perform the 
action. The callback receives the current Request instance and must return true or false.

You can set the `authorize` callback using the constructor or the fluent setter.

```php
<?php

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Actions\MassAction;

new MassAction(
    action: route('users.mass-delete'), 
    authorize: fn (Request $request) => $request->user()->can('delete-users'), 
);
// Or
new MassAction(action: route('users.mass-delete'))
    ->authorize(fn (Request $request) => $request->user()->can('delete-users'));
```

### Confirmation Prompt

Mass actions often perform destructive or irreversible operations. You can enable a confirmation dialog using the 
`confirm` property.

You can set the `confirm` property through the constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Actions\MassAction;

new MassAction(action: route('users.mass-delete'), confirm: __('Are you sure you want to delete the selected users?'));
// Or
new MassAction(action: route('users.mass-delete'))
    ->confirm(__('Are you sure you want to delete the selected users?'));
```

You can also add a second confirmation action by specifying a confirmation value which the user has to enter. You can 
do this be setting the `confirmValue`.

You can set the `confirmValue` through the constructor or the by adding it to the `confirm` method.

```php
<?php

use BrickNPC\EloquentTables\Actions\MassAction;

new MassAction(action: route('users.mass-delete'), confirm: __('Are you sure you want to delete the selected users?'), confirmValue: 'DELETE');
// Or
new MassAction(action: route('users.mass-delete'))
    ->confirm(__('Are you sure you want to delete the selected users?'), 'DELETE');
```

### Tooltip

A tooltip can be added to give more context or help text when hovering over the mass action.

The `tooltip` can be set through the constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Actions\MassAction;

new MassAction(action: route('users.mass-delete'), tooltip: __('This will delete all selected users permanently.'));
// Or
new MassAction(action: route('users.mass-delete'))
    ->tooltip(__('This will delete all selected users permanently.'));
```

## Dependency injection

The `massActions` method supports dependency injection, meaning you can typehint any object in the `massActions`
method and the Eloquent Tables package will inject that dependency into your method.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\MassAction;

class UserTable extends Table
{
    public function massActions(MyService $service, AnotherService $another): array
    {
        return [
            //... Mass action definitions
        ];
    }
}
```

## Route model binding

The `massActions` method also supports Route Model Binding in the same way controller methods do. You can typehint any
Laravel Model in your `massActions` method, and as long as the name of the parameter is the same as the name of the route
parameter, the Eloquent Tables package will load the model from the database and inject it into your `massActions` method.

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
use BrickNPC\EloquentTables\Actions\MassAction;

class UserTable extends Table
{
    public function massActions(Team $team): array
    {
        return [
            //... Mass action definitions
        ];
    }
}
```

Navigating to `http://my-app.test/1/users` will automatically try to load the Team with ID 1 and inject it into the
`massActions` method. If there is no team with the given key, a `404 model not found` exception is thrown just like for
normal route model binding in Laravel.