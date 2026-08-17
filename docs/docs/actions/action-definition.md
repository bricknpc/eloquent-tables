---
sidebar_position: 2
---

# Action definition

To add an action to your table you simply need to initialize an `BrickNPC\EloquentTables\Actions\Action` and return it 
via one of the `XXXActions` methods on your table. 

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;

class UserTable extends Table
{
    public function rowActions(): array
    {
        return [
            new Action(), // Define the action here
        ];
    }
}
```

To make the action actually do something useful it needs three things:

- **An intent**. An intent defines how the action is rendered, and therefor how it looks and how it behaves.
- **A label**. This is the text that is displayed on the action.
- **Zero or more capabilities**. Capabilities are used to either check if an action can be performed or to modify or add behavior to the action.

## Action intent

The action intent defines how the action is rendered, and therefor how it looks and how it behaves. The Eloquent Tables 
package comes with a few predefined intents that should cover most use-cases, but you can also create your own intents.

You can set the intent of an action by calling the `as()` method on the action and providing an intent.

```php
<?php

use BrickNPC\EloquentTables\Actions\Action;
use \BrickNPC\EloquentTables\Actions\Intents\Http;

new Action()
    ->as(new Http(route('users.create')));
```

:::warning

An intent is required. An action without an intent has no behaviour to render, so rendering one throws a
`BrickNPC\EloquentTables\Exceptions\ActionIntentNotSet` exception. If none of the built-in intents fit your use-case,
write a [custom intent](#custom-intents) instead of leaving the intent unset.

:::

### HTTP intent

The HTTP intent is the most common use for an action. It defines that an action is either an http link that is opened or 
a form that is sent.

The HTTP intent accepts two parameters:

- The URL of the link / The action of the form. This parameter can be a string, or a `\Closure` that receives the `ActionContext` so the action can be rendered based on the context.
- The HTTP method to use. If you use `BrickNPC\EloquentTables\Enums\Method::Get` as the method, the intent is used as a simple link. All other methods are used as a form.

#### Example: link to `edit` page on row level

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;
use \BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class UserTable extends Table
{
    public function rowActions(): array
    {
        return [
            new Action()
                ->as(new Http(fn(ActionContext $context) => route('users.edit', ['user' => $context->model]))),
        ];
    }
}
```

#### Example: button to call `delete user` controller on row level

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use \BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class UserTable extends Table
{
    public function rowActions(): array
    {
        $deleteIntent = new Http(
            fn(ActionContext $context) => route('users.delete', ['user' => $context->model]), 
            Method::Delete, 
        );
    
        return [
            new Action()->as($deleteIntent),
        ];
    }
}
```

### Modal intent

The Modal intent renders a button that opens a Bootstrap modal with content that comes from your own application,
for instance a blade view containing a create or an edit form. The content is rendered together with the table, so it
is available the moment the modal is opened. To show content from another application, use the
[HTTP modal intent](#http-modal-intent) instead.

The Modal intent accepts two parameters:

- The title of the modal. This parameter can be a string, or a `\Closure` that receives the `ActionContext`.
- The content of the modal. This parameter is optional and can be a string, or a `\Closure` that receives the `ActionContext`.

Both the title and the content are rendered as HTML, so you can pass markup for a richer modal. Because of that, never
pass unescaped user input into a modal. Use `e()` or a Blade view to escape it first.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Actions\Intents\Modal;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class UserTable extends Table
{
    public function rowActions(): array
    {
        return [
            new Action()
                ->label(__('Details'))
                ->as(new Modal(
                    title: fn(ActionContext $context) => $context->model->name,
                    content: fn(ActionContext $context) => \view('users.details', ['user' => $context->model])->render(),
                )),
        ];
    }
}
```

The modal is rendered with a close button in the header and in the footer. If you need other buttons in the modal, or
a modal that loads its content over http, use the [HTTP modal intent](#http-modal-intent).

:::warning

Do not combine the Modal intent with the [Confirmation capability](#confirmation). Both render a modal, and a
confirmation modal expects to confirm a form that a Modal intent does not have.

:::

### HTTP modal intent

The HTTP modal intent renders a button that opens a Bootstrap modal containing a page from another application. The
page is embedded in an iframe, so it keeps its own styling and its own javascript, and it can not touch the page your
table is on. To show content from your own application, use the [Modal intent](#modal-intent) instead.

The HTTP modal intent accepts two parameters:

- The title of the modal. This parameter can be a string, or a `\Closure` that receives the `ActionContext`.
- The URL that is embedded. This parameter can be a string, or a `\Closure` that receives the `ActionContext`.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Actions\Intents\HttpModal;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class UserTable extends Table
{
    public function rowActions(): array
    {
        return [
            new Action()
                ->label(__('Invoices'))
                ->as(new HttpModal(
                    title: __('Invoices'),
                    url: fn(ActionContext $context) => 'https://invoices.example.com/customers/' . $context->model->uuid,
                )),
        ];
    }
}
```

The URL is only loaded when the modal is opened, so opening a table with a hundred rows does not load a hundred pages.
While the page is loading a spinner is shown. When the modal is closed the URL is unloaded again, so the page is
loaded fresh on the next open and never shows stale data.

:::info

Because the page is embedded in an iframe, everything inside it belongs to the other application. Links and forms
inside the modal navigate inside the iframe, not the page your table is on. Your css does not style it and your
javascript can not reach it. That also means the other application decides whether it can be embedded at all.

:::

:::warning

An application can refuse to be embedded with the `X-Frame-Options` header or the `frame-ancestors` directive of a
`Content-Security-Policy` header. If it does, the browser shows its own message inside the modal instead of the page,
and Eloquent Tables can not detect that or show a friendlier error. Check the headers of the application you want to
embed before you use this intent.

:::

:::warning

Do not combine the HTTP modal intent with the [Confirmation capability](#confirmation). Both render a modal, and a
confirmation modal expects to confirm a form that an HTTP modal intent does not have.

:::

### Custom intents

The built-in intents should be enough to cover most use-cases, but if you have a need to create something custom that is 
also possible.

Create a new intent class that extends `BrickNPC\EloquentTables\Actions\ActionIntent`. This custom intent has one 
method that must be implemented: `view()`. This method must return the name of the blade file that is used to render 
the action. You must also create this view file.

When the action is rendered it uses the blade file defined by the `view()` method to render it. This blade view 
receives the following data:

| Variable name         | Type                                                        | Description                                                                  |
|-----------------------|-------------------------------------------------------------|------------------------------------------------------------------------------|
| `$theme`              | `BrickNPC\EloquentTables\Enums\Theme`                       | An enum case containing the current theme.                                   |
| `$dataNamespace`      | string                                                      | The namespace for data- attributes being used.                               |
| `$context`            | `BrickNPC\EloquentTables\Actions\Contexts\ActionContext`    | The current action context.                                                  |
| `$label`              | string                                                      | The rendered label.                                                          |
| `$beforeContent`      | `BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer` | An object containing data that should be rendered before the action.         |
| `$afterContent`       | `BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer` | An object containing data that should be rendered after the action.          |
| `$renderedAttributes` | `BrickNPC\EloquentTables\Actions\ValueObjects\RenderBuffer` | An object containing all HTML attributes that should be added to the action. |
| `$intent`             | `BrickNPC\EloquentTables\Actions\ActionIntent`              | The intent object itself.                                                    |
| `$id`                 | string                                                      | A random and unique string for each rendered action.                         |

:::note[Bulk actions]

If your custom intent renders a form that is also used as a [bulk action](intro.md#bulk-actions), add the
`data-{namespace}-bulk-action-form="true"` attribute to the element that submits the form, where `{namespace}` is the
`$dataNamespace` variable. The Eloquent Tables javascript uses that attribute to add the keys of the selected rows to
the form before it is submitted.

```bladehtml
<button type="submit"
        @if($context->isBulk) data-{{ $dataNamespace }}-bulk-action-form="true" @endif
        form="{{ $id }}"
>{!! $label !!}</button>
```

:::

```php
<?php
declare(strict_types=1);

namespace App\Tables\Intents;

use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class MyIntent extends ActionIntent
{
    public function view(): string
    {
        return 'tables.intents.my-intent';
    }
}
```

```bladehtml
{{-- resources/views/tables/intents/my-intent.blade.php --}}
This is where you should render the action
```

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Tables\Intents\MyIntent;
use BrickNPC\EloquentTables\Table;

class UserTable extends Table
{
    public function rowActions(): array
    {
        return [
            new Action()->as(new MyIntent()),
        ];
    }
}
```

#### Render hooks

Every intent, custom or built-in, has two optional hooks that run around the render. Both are set with a closure that
receives the action's descriptor and the current context, and both are optional: an intent that sets neither renders
exactly as before.

| Method                | Runs                                                                       |
|-----------------------|----------------------------------------------------------------------------|
| `before(\Closure)`    | after every capability has been applied, before the view receives its data |
| `after(\Closure)`     | after the view has been created                                            |

```php
<?php
declare(strict_types=1);

namespace App\Tables\Intents;

use BrickNPC\EloquentTables\Actions\ActionIntent;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class MyIntent extends ActionIntent
{
    public function __construct()
    {
        $this->before(function (ActionDescriptor $descriptor, ActionContext $context): void {
            $descriptor->attributes['data-row'] = (string) $context->model?->getKey();
        });
    }

    public function view(): string
    {
        return 'tables.intents.my-intent';
    }
}
```

Because the hooks run on the descriptor, they can do anything a [capability](#capabilities) can do. Prefer a capability
when the behaviour is reusable across intents, and a hook when it is inherent to one intent.

:::warning

Use `before()` for anything that must show up in the output. By the time `after()` runs, `$descriptor->attributes` has
already been copied into the view's data, so changes made there are lost. Blade renders lazily, so writes to the render
buffers (`beforeRender`, `afterRender`, `attributesRender`) do still land in the output because those are objects,
which makes `after()` inconsistent about what it can affect. Treat it as a place to clean up or record state, not to
influence the markup.

:::

## Label

The label is just the text that is displayed on the link or button that triggers the action. Like with intents, the 
label can either be a string or a `\Closure` that receives the `ActionContext`. Set the label through the `label` method 
on the action.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use \BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class UserTable extends Table
{
    public function rowActions(): array
    {
        return [
            new Action()->as(...)->label(fn(ActionContext $context) => __('Delete :name', ['name' => $context->model->name])),
            new Action()->as(...)->label('Open details'),
        ];
    }
}
```

## Capabilities

Capabilities define what the action is capable of. This can be any combination of these three things:

- **Check**. A capability can check whether it should be displayed, for instance an `auth` check.
- **Apply**. A capability can change the action before it renders, for instance setting an attribute on it.
- **Contribute**. A capability can contribute to the rendering of the action, for instance adding a tooltip to a button.

The Eloquent Tables package provides four built-in capabilities that should cover most use-cases, though you are of course 
free to create and add your own capabilities:

- **Authorize**. Used to check if the user has permission to see the action.
- **Confirmation**. Used to add a confirmation modal to the action.
- **Tooltip**. Used to add a tooltip to the action.
- **When**. Used to determine whether the action should be rendered. Similar to authorize.

Styling is not a capability. It is a method on the action itself, like `label()`. See
[action styling](../styling/action-styling.md).

You can add capabilities by calling the `with` method on an action and adding the capability object. You can add as 
many capabilities as you need.

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Actions\Capabilities\Tooltip;
use BrickNPC\EloquentTables\Actions\Capabilities\Authorize;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class UserTable extends Table
{
    public function tableActions(): array
    {
        return [
            new Action()
                ->as(...)
                ->label(...)
                ->with(new Authorize(fn(ActionContext $context) => $context->request->user()->can('create', User::class)))
                ->with(new Tooltip('Create a new user')),
        ];
    }
}
```

### Authorize

The Authorize capability expects a `\Closure` that should return a boolean indicating whether the current user has 
permissions to see the action. The closure receives the `ActionContext` object.

```php
<?php

use BrickNPC\EloquentTables\Actions\Capabilities\Authorize;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

new Authorize(fn(ActionContext $context) => $context->request->user()->can('edit', $context->model));
```

### Confirmation

The Confirmation capability adds a confirmation modal to the action. It expects one required and three optional parameters 
that define what the modal looks like and how it behaves.

| Parameter                 | Required | Type                                                 | Description                                                                                                          |
|---------------------------|----------|------------------------------------------------------|----------------------------------------------------------------------------------------------------------------------|
| `$text`                   | yes      | string or `\Closure(ActionContext $context): string` | The text that is displayed in the modal.                                                                             |
| `$confirmValue`           | no       | string or `\Closure(ActionContext $context): string` | The text that is displayed on the confirm button.                                                                    |
| `$cancelValue`            | no       | string or `\Closure(ActionContext $context): string` | The text that is displayed on the cancel button.                                                                     |
| `$inputConfirmationValue` | no       | string or `\Closure(ActionContext $context): string` | An optional extra word or phrase that the user must exactly type into a text field for the confirmation to be valid. |

```php
<?php

use BrickNPC\EloquentTables\Actions\Capabilities\Confirmation;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

new Confirmation(
    'Are you sure?',
    'Yes, I\'m sure',
    'No, take me back',
    'DELETE',
);
```

### Tooltip

The Tooltip capability expects a string or `\Closure` that should return the text for the tooltip. The closure receives 
the `ActionContext` object.

```php
<?php

use BrickNPC\EloquentTables\Actions\Capabilities\Tooltip;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

new Tooltip(fn(ActionContext $context) => __('Edit the details of :name', ['name' => $context->model->name]));
```

### When

The When capability expects a `\Closure` that should return a boolean indicating whether the action should be rendered. The 
closure receives the `ActionContext` object.

```php
<?php

use BrickNPC\EloquentTables\Actions\Capabilities\When;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

new When(fn(ActionContext $context) => $contex->model->is_active);
```

### Custom capabilities

To create your own custom capability you simply create a new class that extends the `BrickNPC\EloquentTables\Actions\ActionCapability`
class. This `ActionCapability` has default implementations for all types of capabilities and does nothing by default.

```php
<?php
declare(strict_types=1);

namespace App\Tables\Capabilities;

use BrickNPC\EloquentTables\Actions\ActionCapability;

class MyCapability extends ActionCapability
{
    // ...
}
```

#### Check capability

A capability that checks if the action should be rendered must overwrite the `check` method.

```php
<?php
declare(strict_types=1);

namespace App\Tables\Capabilities;

use BrickNPC\EloquentTables\Actions\ActionCapability;

class MyCapability extends ActionCapability
{
    public function check(ActionDescriptor $descriptor, ActionContext $context): bool
    {
        return true; // Return true or false based on your capability conditions
    }
}
```

#### Apply capability

A capability that changes the action before it renders should overwrite the `apply` method. It receives the descriptor
of the action and may change anything on it.

```php
<?php
declare(strict_types=1);

namespace App\Tables\Capabilities;

use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\ActionDescriptor;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

class TestAttribute extends ActionCapability
{
    public function apply(ActionDescriptor $descriptor, ActionContext $context): void
    {
        $descriptor->attributes['data-test'] = 'user-action';
    }
}
```

:::warning[Warning]

Do not set the `class` attribute this way. The class of an action is rendered from its style set, so a `class` in the
attributes bag is emitted a second time and the browser ignores it. Add to `$descriptor->style` instead:

```php
$descriptor->style = $descriptor->style?->with(ButtonStyle::Danger) ?? new StyleSet(ButtonStyle::Danger);
```

:::

#### Contribute capability

A capability that contributes to the looks or behavior of the method should overwrite the `contribute` method.

```php
<?php
declare(strict_types=1);

namespace App\Tables\Capabilities;

use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;

class MyCapability extends ActionCapability
{
    public function contribute(ActionDescriptor $descriptor, ActionContext $context): ?CapabilityContribution
    {
        return null;
    }
}
```

A contributing capability is a special capability in that it needs to return a `BrickNPC\EloquentTables\Actions\CapabilityContribution`
object. This is because a contribution can be to the action itself, but also something that needs to be rendered 
before or after the action, like modal HTML for instance.

To create a `CapabilityContribution` create a new class that extends `BrickNPC\EloquentTables\Actions\CapabilityContribution`.

```php
<?php
declare(strict_types=1);

namespace App\Tables\CapabilityContributions;

use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;

class MyCapabilityContribution extends CapabilityContribution
{
    // ...
}
```

Depending on what your capability contributes, you need to overwrite one or more of the `renderBefore`, `renderAttributes`
or `renderAfter` method. The `renderBefore` and `renderAfter` methods should return a string, HTML or a view that should 
be rendered before or after the action respectively.

The `renderAttributes` method should return a string with all the HTML attributes that should be added to the action.

```php
<?php
declare(strict_types=1);

namespace App\Tables\CapabilityContributions;

use BrickNPC\EloquentTables\Actions\ActionCapability;
use BrickNPC\EloquentTables\Actions\CapabilityContribution;

class MyCapabilityContribution extends CapabilityContribution
{
    public function renderBefore(
        ActionDescriptor $descriptor,
        ActionContext $context,
    ): Htmlable|string|\Stringable|View|null {
        return null;
    }

    public function renderAttributes(
        ActionDescriptor $descriptor,
        ActionContext $context,
    ): string|\Stringable|View|null {
        return null;
    }

    public function renderAfter(
        ActionDescriptor $descriptor,
        ActionContext $context,
    ): Htmlable|string|\Stringable|View|null {
        return null;
    }
}
```