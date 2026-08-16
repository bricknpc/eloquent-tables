---
sidebar_position: 16
---

# Upgrading to 2.0

Version 2.0 rewrites the actions system, changes the query parameters every table reads, and replaces how styling is
declared.

**Every 2.0 upgrade needs at least one change.** Tables now namespace their query parameters under a table name, so
`?sort[name]=asc` becomes `?user[sort][name]=asc`. Any URL you have hard-coded, linked to, bookmarked or documented
needs updating, and the `pageName` and `perPageName` properties are gone. If your tables also define `tableActions()`,
`rowActions()` or `massActions()`, every one of those definitions needs to be rewritten as well.

Sorting behaviour changed too: a multi-column sort now applies in the order the visitor clicked the headers rather than
the order the columns are declared. And every styling method was replaced by a single `style()` shape, so
`styles()`, `cellStyles()`, `tableStyles()` and `pageStyle()` all need rewriting.

The requirements are unchanged: PHP `^8.4|^8.5`, Laravel `^12.0` and Bootstrap 5.

## What changed

In 1.x there were three action classes, each with its own constructor, its own set of options and its own view builder:
`TableAction`, `RowAction` and `MassAction`. Adding a feature to one meant adding it to the other two, and the three
classes had quietly drifted apart: `RowAction::tooltip()` accepted a closure, `MassAction::tooltip()` did not.

2.0 replaces all three with a single `Action` class built from two pieces:

- an **intent**, set with `->as()`, which decides what the action *does* and which view renders it
- any number of **capabilities**, added with `->with()`, which decide whether the action renders and what it looks like

The action type is no longer encoded in the class. It comes from the method you return it from, so the same `Action` can
be used as a table action, a row action or a bulk action.

```php
<?php

use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\Actions\Capabilities\Style;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

new Action()
    ->label(__('Delete'))
    ->as(new Http(fn (ActionContext $context) => route('users.destroy', $context->model), Method::Delete))
    ->with(new Style(ButtonStyle::Danger));
```

See [Action definition](actions/action-definition.md) for the full reference.

## 1. Rename `massActions()` to `bulkActions()`

"Mass action" is now "bulk action" throughout the package.

```php
// 1.x
public function massActions(): array

// 2.0
public function bulkActions(): array
```

A `massActions()` method is no longer called, and it fails silently: the table simply renders without bulk actions and
without the checkbox column. There is no deprecation warning, so search your project for `massActions` before you
upgrade.

If you have published the views or written custom JavaScript against the table markup, note that the browser-facing
names changed with it: `data-{namespace}-mass-action-form` is now `data-{namespace}-bulk-action-form`,
`id="mass-action-switch-..."` is now `id="bulk-action-switch-..."`, and the `table-mass-actions` class is now
`table-bulk-actions`.

## 2. Rewrite your actions

`TableAction`, `RowAction` and `MassAction` are deleted. Every option maps onto an intent or a capability:

| 1.x                                     | 2.0                                                            |
|-----------------------------------------|----------------------------------------------------------------|
| `action: $url`                          | `->as(new Http($url))`                                          |
| `action: $url` + `asForm()` / `post()`  | `->as(new Http($url, Method::Post))`                            |
| `method: Method::Delete`                | second argument of `Http`                                       |
| `label: $label`                         | `->label($label)` (unchanged)                                   |
| `tooltip: $text`                        | `->with(new Tooltip($text))`                                    |
| `styles: [ButtonStyle::Danger]`         | `->with(new Style(ButtonStyle::Danger))` (variadic, no array)   |
| `authorize: $closure`                   | `->with(new Authorize($closure))`                               |
| `when: $closure`                        | `->with(new When($closure))`                                    |
| `confirm: $text`                        | `->with(new Confirmation($text))`                               |
| `confirmValue: $value`                  | fourth argument of `Confirmation` (see the warning below)       |
| `asModal()`                             | `->as(new Modal(...))` or `->as(new HttpModal(...))`            |

Two details are easy to miss:

**`Style` is variadic.** In 1.x styles were an array; in 2.0 they are separate arguments:
`new Style(ButtonStyle::Danger, ButtonStyle::Link)`.

**Every action needs an intent.** An `Action` without `->as()` throws `ActionIntentNotSet` when it renders, and calling
`->as()` twice on the same action throws `ActionIntentAlreadySet`. In 1.x the URL was a constructor argument, so this
could not happen.

## 3. Closures now receive an `ActionContext`

This is the change most likely to break silently, because the old closures took a model and the new ones take an object
that *contains* the model.

In 1.x, the arguments differed per class and per option: a `RowAction` URL closure received the model, its `authorize`
closure received the request *and* the model, and a `MassAction` `authorize` closure received only the request.

In 2.0 every closure receives exactly one argument, a `BrickNPC\EloquentTables\Actions\Contexts\ActionContext`:

```php
// 1.x
new RowAction(
    action: fn (User $user) => route('users.edit', $user),
    authorize: fn (Request $request, User $user) => $request->user()->can('update', $user),
);

// 2.0
new Action()
    ->as(new Http(fn (ActionContext $context) => route('users.edit', $context->model)))
    ->with(new Authorize(fn (ActionContext $context) => $context->request->user()->can('update', $context->model)));
```

`$context->model` is `null` for table actions and bulk actions, which have no row to bind to. See
[Action context](actions/action-context.md) for every available property.

## 4. `confirmValue` changed meaning

:::warning

`confirmValue` exists in both versions and means something different in each. Migrating it by name gives you a working
table with the wrong behaviour, and no error.

:::

In 1.x, `confirmValue` was the phrase the user had to type into a text field to confirm a destructive action. In 2.0 the
second argument of `Confirmation` is called `$confirmValue` but is the **label of the confirm button**. The typed-phrase
behaviour is now the fourth argument, `$inputConfirmationValue`:

```php
// 1.x: the user must type "DELETE"
new RowAction(action: $url)->confirm('Are you sure?', 'DELETE');

// 2.0: the user must type "DELETE"
new Action()
    ->as(new Http($url, Method::Delete))
    ->with(new Confirmation(
        text: 'Are you sure?',
        inputConfirmationValue: 'DELETE',
    ));
```

Using named arguments here is worth the extra keystrokes.

## 5. `asModal()` now does something

`TableAction::asModal()` existed in 1.x but the flag was never read by any view, so it rendered a plain link. If you
called it, you were not getting a modal.

2.0 has two real modal intents: `Modal`, which takes a title and inline content, and `HttpModal`, which takes a title
and a URL and loads that URL into the modal. Both are documented under
[Action intent](actions/action-definition.md#modal-intent).

## 6. Republish the views

If you published the package views on 1.x, the action views under `resources/views/vendor/eloquent-tables/` are stale.
These files were deleted:

- `bootstrap-5/action/table-action.blade.php`
- `bootstrap-5/action/row-action.blade.php`
- `bootstrap-5/action/mass-action.blade.php`

They are replaced by a set of views under `actions/`: one per intent, one per capability contribution, plus the
collection views. Delete the old `action/` directory and republish:

```bash
php artisan vendor:publish --provider="BrickNPC\EloquentTables\Providers\EloquentTablesServiceProvider" --tag="views" --force
```

Then reapply any customisations you had made.

Three of the variables the table views receive were renamed along with their classes. If you customised `thead`,
`tbody` or `header`, update these:

| 1.x variable              | 2.0 variable            |
|---------------------------|-------------------------|
| `$columnLabelViewBuilder` | `$columnLabelRenderer`  |
| `$columnValueViewBuilder` | `$columnValueRenderer`  |
| `$filterViewBuilder`      | `$filterRenderer`       |

The method on each is still `build()`, so only the variable name changes.

## 7. Removed `BrickNPC\EloquentTables\view()`

The `BrickNPC\EloquentTables\view(Theme $theme, string $view)` helper is gone. It built a theme-namespaced view name and
was never used by the package itself. The same thing is now a method on the theme:

```php
// removed
BrickNPC\EloquentTables\view($theme, 'actions.modal');

// 2.0
$theme->view('actions.modal');
```

This only affects you if you wrote custom theme views that called the helper. The `actions()`, `dropdownActions()` and
`groupedActions()` helpers are unchanged.

## 8. Query parameters are namespaced per table

Every parameter a table reads now lives under the table's name, so two tables on one page no longer read each other's
values. In 1.x they shared `?search=`, `?sort[...]` and `?filter[...]`, which meant sorting one sorted both.

| Concern  | 1.x                  | 2.0                          |
|----------|----------------------|------------------------------|
| Search   | `?search=ada`        | `?user[search]=ada`          |
| Sort     | `?sort[email]=asc`   | `?user[sort][email]=asc`     |
| Filter   | `?filter[active]=1`  | `?user[filter][active]=1`    |
| Per page | `?per_page=50`       | `?user[per_page]=50`         |
| Page     | `?page=3`            | `?user[page]=3`              |

The name comes from `Table::name()`, which defaults to the class name with a trailing `Table` removed and the rest
snake-cased, so `UserTable` becomes `user` and `ArchivedUserTable` becomes `archived_user`. Override it to choose your
own:

```php
<?php

class UserTable extends Table
{
    public function name(): string
    {
        return 'users';
    }
}
```

:::warning
Two tables of the same class on one page share a name, and therefore share both their query parameters and their
stored preferences. Give at least one of them its own `name()`. The browser console warns when it spots a duplicate.
:::

Update any hard-coded link, bookmark or test that builds a table URL by hand.

## 9. `pageName` and `perPageName` are gone

Both properties are removed. The table name already separates one table's parameters from another's, so a second
identifier is no longer needed. The sub-key names moved to config, alongside the search, sorting and filtering names
that were already there:

```php
// config/eloquent-tables.php
'pagination' => [
    'page_query_name'     => 'page',
    'per_page_query_name' => 'per_page',
],
```

```php
<?php

class UserTable extends Table
{
    use WithPagination;

    // Both of these are removed in 2.0. Delete them.
    protected string $pageName    = 'users-page';
    protected string $perPageName = 'items';
}
```

`perPage`, `perPageOptions` and `perPage(Request $request)` are unchanged.

## 10. Multi-column sort follows the click order

In 1.x the sort was applied in the order the columns were declared on the table, whatever order the visitor clicked the
headers in. Clicking email and then name produced the same ordering as clicking name and then email.

2.0 applies the sort in the order it was built up. If you relied on column order to fix the precedence, sort in your
`query()` instead, or give the column a default sort.

An unrecognised sort direction is now ignored rather than throwing, so a hand-edited `?user[sort][name]=sideways` no
longer returns a 500.

## 11. Republish the views if you have published them

The search, per-page and filter controls now carry hidden inputs so that using one no longer discards the rest of the
table's state, and the table element carries the table name for the JavaScript. Every cell also gained a flex wrapper,
which is what makes alignment work the same way everywhere. A published copy from 1.x keeps the old markup and will
silently lose both.

```bash
php artisan vendor:publish --tag=views --force
```

`ColumnLabelRenderer::build()` and `FilterRenderer::build()` both take the table as an argument now, and
`RowsBuilder`'s constructor no longer takes `Config`. This only matters if you resolve or subclass them yourself.

## 12. Column styling is one method

`styles()` and `cellStyles()` are replaced by `style()`, which takes any number of `CellStyle` cases and an optional
closure. The `styles` and `cellStyles` constructor arguments are gone with them.

```php
new Column('total')->styles(TableStyle::Success)->cellStyles(CellStyle::AlignRight);  // 1.x
new Column('total')->style(CellStyle::BackgroundSuccess, CellStyle::AlignRight);      // 2.0
```

`CellStyle` grew from eight alignment cases to also cover backgrounds, text colours and font weights, so anything you
used to reach for `TableStyle` on a column for now has a cell equivalent. `TableStyle` is table-level only.

The closure is new: it receives a `CellContext` and can style a cell by its value. See
[cell styling](styling/cell-styling.md).

## 13. `tableStyles()` becomes `style()`

Table-level styling takes the same shape as everything else.

```php
public function tableStyles(): array { return [TableStyle::Striped]; }  // 1.x
public function style(): array      { return [TableStyle::Striped]; }  // 2.0
```

## 14. `pageStyle()` becomes `accentStyle()`

It never governed a page. It governs the table's own controls, so it is named for that now. `PageStyle` is replaced by
`AccentStyle`, with the same cases.

```php
public function pageStyle(): PageStyle       { return PageStyle::Primary; }     // 1.x
public function accentStyle(): AccentStyle   { return AccentStyle::Primary; }   // 2.0
```

The accent is a single colour, so the method returns one case. Anything conditional goes in the method body, which
already has the request.

## Full example

A 1.x table with all three action types:

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\RowAction;
use BrickNPC\EloquentTables\Actions\MassAction;
use BrickNPC\EloquentTables\Actions\TableAction;

class UserTable extends Table
{
    public function tableActions(): array
    {
        return [
            new TableAction(action: route('users.create'), label: __('Add user')),
        ];
    }

    public function rowActions(): array
    {
        return [
            new RowAction(action: fn (User $user) => route('users.edit', $user))
                ->label(__('Edit'))
                ->tooltip(fn (User $user) => __('Edit :name', ['name' => $user->name])),

            new RowAction(action: route('users.destroy'), styles: [ButtonStyle::Danger])
                ->label(__('Delete'))
                ->delete()
                ->authorize(fn (Request $request, User $user) => $request->user()->can('delete', $user))
                ->confirm(__('Are you sure?'), 'DELETE'),
        ];
    }

    public function massActions(): array
    {
        return [
            new MassAction(action: route('users.bulk-destroy'), label: __('Delete selected'))
                ->delete()
                ->confirm(__('Delete all selected users?')),
        ];
    }
}
```

The same table in 2.0:

```php
<?php
// app/Tables/UserTable.php
declare(strict_types=1);

namespace App\Tables;

use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\Method;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Actions\Intents\Http;
use BrickNPC\EloquentTables\Actions\Capabilities\Style;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;
use BrickNPC\EloquentTables\Actions\Capabilities\Tooltip;
use BrickNPC\EloquentTables\Actions\Capabilities\Authorize;
use BrickNPC\EloquentTables\Actions\Capabilities\Confirmation;

class UserTable extends Table
{
    public function tableActions(): array
    {
        return [
            new Action()
                ->label(__('Add user'))
                ->as(new Http(route('users.create'))),
        ];
    }

    public function rowActions(): array
    {
        return [
            new Action()
                ->label(__('Edit'))
                ->as(new Http(fn (ActionContext $context) => route('users.edit', $context->model)))
                ->with(new Tooltip(fn (ActionContext $context) => __('Edit :name', ['name' => $context->model->name]))),

            new Action()
                ->label(__('Delete'))
                ->as(new Http(route('users.destroy'), Method::Delete))
                ->with(new Style(ButtonStyle::Danger))
                ->with(new Authorize(
                    fn (ActionContext $context) => $context->request->user()->can('delete', $context->model),
                ))
                ->with(new Confirmation(
                    text: __('Are you sure?'),
                    inputConfirmationValue: 'DELETE',
                )),
        ];
    }

    public function bulkActions(): array
    {
        return [
            new Action()
                ->label(__('Delete selected'))
                ->as(new Http(route('users.bulk-destroy'), Method::Delete))
                ->with(new Confirmation(__('Delete all selected users?'))),
        ];
    }
}
```

## Removed and renamed API

| Removed                                              | Replacement                                          |
|------------------------------------------------------|------------------------------------------------------|
| `BrickNPC\EloquentTables\Actions\TableAction`         | `BrickNPC\EloquentTables\Actions\Action`             |
| `BrickNPC\EloquentTables\Actions\RowAction`           | `BrickNPC\EloquentTables\Actions\Action`             |
| `BrickNPC\EloquentTables\Actions\MassAction`          | `BrickNPC\EloquentTables\Actions\Action`             |
| `BrickNPC\EloquentTables\Builders\TableActionViewBuilder` | `BrickNPC\EloquentTables\Actions\ActionRenderer` |
| `BrickNPC\EloquentTables\Builders\RowActionViewBuilder`   | `BrickNPC\EloquentTables\Actions\ActionRenderer` |
| `BrickNPC\EloquentTables\Builders\MassActionViewBuilder`  | `BrickNPC\EloquentTables\Actions\ActionRenderer` |
| `BrickNPC\EloquentTables\Builders\TableViewBuilder`       | `BrickNPC\EloquentTables\Tables\TableRenderer`   |
| `BrickNPC\EloquentTables\Builders\ColumnLabelViewBuilder` | `BrickNPC\EloquentTables\Columns\ColumnLabelRenderer` |
| `BrickNPC\EloquentTables\Builders\ColumnValueViewBuilder` | `BrickNPC\EloquentTables\Columns\ColumnValueRenderer` |
| `BrickNPC\EloquentTables\Builders\FilterViewBuilder`      | `BrickNPC\EloquentTables\Filters\FilterRenderer` |
| `BrickNPC\EloquentTables\view()`                      | `Theme::view()`                                      |
| `massActions()` table method                          | `bulkActions()`                                      |
| `pageName` table property                             | `eloquent-tables.pagination.page_query_name` config  |
| `perPageName` table property                          | `eloquent-tables.pagination.per_page_query_name` config |
| `Column::styles()` and the `styles` argument          | `Column::style()`                                    |
| `Column::cellStyles()` and the `cellStyles` argument  | `Column::style()`                                    |
| `Table::tableStyles()`                                | `Table::style()`                                     |
| `Table::pageStyle()`                                  | `Table::accentStyle()`                               |
| `BrickNPC\EloquentTables\Enums\PageStyle`            | `BrickNPC\EloquentTables\Enums\AccentStyle`         |

The `Table::$builder` property was renamed to `Table::$renderer` along with them.

`BrickNPC\EloquentTables\Builders\RowsBuilder` keeps its name and location. It builds the row data rather than markup,
so it is not a renderer.

If you resolve any of these out of the container or typehint them, update the class name. If you only define tables,
none of this affects you.

## New in 2.0

Things that have no 1.x equivalent, and that you may want once you have upgraded:

- [Action collections](actions/action-collections.md): group actions, or collapse them into a dropdown
- [Modal and HTTP modal intents](actions/action-definition.md#modal-intent)
- [Custom intents](actions/action-definition.md#custom-intents) and
  [custom capabilities](actions/action-definition.md#custom-capabilities)
- The [`When` capability](actions/action-definition.md#when), previously available only on row actions
- [Table names](table-names.md): a stable identity per table, so two tables on a page stay independent
- [Saved preferences](preferences.md): a visitor's per-page choice and multi-column sort survive navigating away and back
- [Cell styling](styling/cell-styling.md): backgrounds, text colours and weights, and styling a cell by its value
- [Row styling](styling/table-styling.md): highlight a whole row, including its checkbox and action cells
- [Conditional action styling](styling/action-styling.md#styling-an-action-by-its-row): the `Style` capability now
  also takes closures, so an action can be styled per row
