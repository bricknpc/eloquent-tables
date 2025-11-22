---
sidebar_position: 2
---

# Row Actions

Row actions are actions that impact a single row on the table. For instance, if you want an 'Edit' or 'Delete' option,
you can add a Row Action for this. Adding a row action will add a column to the end of the table, containing all 
row actions.

To add row actions, implemented the `rowActions` method on your table. This method must return an array of
`BrickNPC\EloquentTables\Actions\RowAction` objects.

## Options

Row actions  support a wide range of customization options, including dynamic URLs, conditional visibility, 
authorization, confirmation prompts, HTTP methods, and more.

### Action

Every row action requires an action. An action is either a string or a Closure that returns a string. If a Closure is 
used for the action, it will receive the Model that is displayed in the row so you may dynamically build the action.

```php
<?php

use BrickNPC\EloquentTables\Actions\RowAction;

new RowAction(action: route('users.teams'));
// Or
new RowAction(action: fn (User $user) => route('users.edit', ['user' => $user]));
```

### Label

The label is the content displayed on the action button within each row. A label can be a string, a Stringable object 
or a HtmlAble object. 

You can set the `label` through the constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Actions\RowAction;

new RowAction(
    action: fn (User $user) => route('users.edit', $user),
    label: __('Edit')
);
// Or
new RowAction(fn (User $user) => route('users.edit', $user))
    ->label(__('Edit'));
```

### Tooltip

Row actions support tooltips, which can either be static or generated per model using a closure. If a Closure is 
provided as a tooltip, it receives the model of the current row, and it should return a string.

You can set the `tooltip` through the constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Actions\RowAction;

new RowAction(action: '', tooltip: (fn (User $user) => __('Edit :name', ['name' => $user->name]));
// Or
new RowAction(action: '')->tooltip(fn (User $user) => __('Edit :name', ['name' => $user->name]));
```

### Styles

You may assign any number of `BrickNPC\EloquentTables\Enums\ButtonStyle` enum values to customize the visual appearance 
of the row action.

You can set the `styles` through the constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Actions\RowAction;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

new RowAction(action: '', styles: [ButtonStyle::Success]);
// Or
new RowAction(action: '')->styles(ButtonStyle::Success);
```

### Form Submission (`asForm`)

Row actions can be both links and forms. By default, a Row action is a link, ti change it to a form set the `asForm` to 
`true`.

You can set the `asForm` through the constructor or the fluent setter.

```php
<?php

use BrickNPC\EloquentTables\Actions\RowAction;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

new RowAction(action: '', asForm: true);
// Or
new RowAction(action: '')->asForm();
```

### Form method

When setting a Row Action to a form, you can also set the HTTP method used to submit the form. By default, forms are 
set to use `POST`. The `method` property accepts one of the `BrickNPC\EloquentTables\Enums\Method` enum cases.

You can set the `method` through the constructor or the fluent setter, through the `asForm()` method or through one 
of the helper methods.

```php
<?php

use BrickNPC\EloquentTables\Actions\RowAction;
use BrickNPC\EloquentTables\Enums\Method;

new RowAction(action: '', asForm: true, method: Method::Put);
// Or
new RowAction(action: '')->asForm(method: Method::Put);
// Or
new RowAction(action: '')->get();
new RowAction(action: '')->post();
new RowAction(action: '')->put();
new RowAction(action: '')->patch();
new RowAction(action: '')->delete();
```

> Using the helper methods automatically sets the `asForm` property to true as well.

### Authorization

Not everybody will be authorised to perform every row action. You can therefore add an authorisation callback to a 
Row action. The authorisation callback is a Closure that receives the current request and the model of the current 
row, and it should return a boolean.

You can set the `authorize` property through the constructor or the fluent setter.

```php
<?php

use App\Models\User;
use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Actions\RowAction;

new RowAction(action: '', authorize: fn(Request $request, Team $team) => $request->user()->can('edit', $team));
// Or
new RowAction(action: '')->authorize(fn(Request $request, Team $team) => $request->user()->can('edit', $team));
```

### Conditional Visibility (`when`)

Not every row action should be visible on each row. An activate or deactivate action for instance should only be shown 
when appropriate. You can define a `when` callback on the Row Action that determines whether the action is shown. The 
`when` callback is a Closure that receives the model of the current row, and should return a boolean value.

You can set the `when` property through the constructor or the fluent setter.

```php
<?php

use App\Models\Team;
use BrickNPC\EloquentTables\Actions\RowAction;

new RowAction(action: route('team.deactivate'), when: fn(Team $team) => $team->active);
// Or
new RowAction(action: route('team.deactivate'))->when(fn(Team $team) => $team->active);
```

### Confirmation Prompt

You can add a confirmation modal to a row action to make sure the user confirms an action before it is performed. 
Confirmation actions work with both link- as form row actions. A confirmation action can be a string, or a Closure that 
returns a string. If a closure is provided as confirm value, it receives the model of the current row, and it should 
return a string.

You can set the `confirm` property through the constructor or the fluent setter.

```php
<?php

use App\Models\Team
use BrickNPC\EloquentTables\Actions\RowAction;

new RowAction(action: route('team.delete'), confirm: fn(Team $team) => __('Are you sure you want to delete :name?', ['name' => $team->name]));
// Or
new RowAction(action: route('team.delete'))->confirm(fn(Team $team) => __('Are you sure you want to delete :name?', ['name' => $team->name]));
```

You can also add a second confirmation action by specifying a confirmation value which the user has to enter. You can
do this be setting the `confirmValue`.

You can set the `confirmValue` through the constructor or the by adding it to the `confirm` method.

## Dependency injection

The `rowActions` method supports dependency injection, meaning you can typehint any object in the `rowActions`
method and the Eloquent Tables package will inject that dependency into your method.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\RowAction;

class UserTable extends Table
{
    public function rowActions(MyService $service, AnotherService $another): array
    {
        return [
            //... Row action definitions
        ];
    }
}
```

## Route model binding

The `rowActions` method also supports Route Model Binding in the same way controller methods do. You can typehint any
Laravel Model in your `rowActions` method, and as long as the name of the parameter is the same as the name of the route
parameter, the Eloquent Tables package will load the model from the database and inject it into your `rowActions` method.

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
use BrickNPC\EloquentTables\Actions\RowAction;

class UserTable extends Table
{
    public function rowActions(Team $team): array
    {
        return [
            //... Row action definitions
        ];
    }
}
```

Navigating to `http://my-app.test/1/users` will automatically try to load the Team with ID 1 and inject it into the
`rowActions` method. If there is no team with the given key, a `404 model not found` exception is thrown just like for
normal route model binding in Laravel.
