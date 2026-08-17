---
sidebar_position: 2
---

# Action Styling

Action styles define the look and feel of actions. They add styling to your buttons and links based on the current theme 
(for now only Bootstrap 5).

You apply them with the `style()` method, which accepts one or more `BrickNPC\EloquentTables\Enums\ButtonStyle` enum
cases, and closures that return them. It is the same method [columns](cell-styling.md) use, with its own vocabulary.

```php
<?php

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

new Action()
    ->label(__('Delete'))
    ->as(...)
    ->style(ButtonStyle::DangerOutline);
```

## Available styles

Every style except `Default` and `Link` has a normal and an outlined variant, for instance `ButtonStyle::Danger` and
`ButtonStyle::DangerOutline`. A style is rendered as the `btn-` class of the theme, so `ButtonStyle::Danger` becomes
`btn-danger` and `ButtonStyle::DangerOutline` becomes `btn-outline-danger`.

| Style        | Outlined variant    |
|--------------|---------------------|
| `Default`    | -                   |
| `Primary`    | `PrimaryOutline`    |
| `Secondary`  | `SecondaryOutline`  |
| `Tertiary`   | `TertiaryOutline`   |
| `Quaternary` | `QuaternaryOutline` |
| `Success`    | `SuccessOutline`    |
| `Warning`    | `WarningOutline`    |
| `Danger`     | `DangerOutline`     |
| `Info`       | `InfoOutline`       |
| `Light`      | `LightOutline`      |
| `Dark`       | `DarkOutline`       |
| `Link`       | -                   |

:::note

The `Tertiary` and `Quaternary` styles are not part of Bootstrap 5. They render as `btn-tertiary` and
`btn-quaternary`, so they only do something when you define those classes in your own css.

:::

## Actions without a style

An action that declares no style is rendered with the `btn-primary` class of the theme, so every action looks the
same until you say otherwise. `ButtonStyle::Default` means the same thing, so you can return it from a closure to say
"leave this one alone".

## Actions inside a dropdown

An action inside an [action collection](../actions/action-collections.md) of the dropdown type is rendered as a menu
item instead of a button. A button style would look out of place there, so only the colour of the style is used:
`ButtonStyle::Danger` and `ButtonStyle::DangerOutline` both render as `text-danger` inside a dropdown.

## Combining styles

`style()` accepts more than one style, and calling it again adds to what is already there rather than replacing it.
Everything is rendered in the order you declared it.

```php
<?php

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ButtonStyle;

new Action()->style(ButtonStyle::Danger, ButtonStyle::Link);

// The same thing, declared in two steps.
new Action()->style(ButtonStyle::Danger)->style(ButtonStyle::Link);
```

## Styling an action by its row

Pass a closure alongside the static styles. It receives a
`BrickNPC\EloquentTables\Actions\Contexts\ActionContext` and returns a case, a list of cases, or `null`. It is the same
shape [cell styling](cell-styling.md#styling-a-cell-by-its-value) uses:

```php
<?php

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\Contexts\ActionContext;

new Action()
    ->label(__('Delete'))
    ->as(...)
    ->style(function (ActionContext $context): ButtonStyle {
        return $context->model?->is_locked
            ? ButtonStyle::SecondaryOutline
            : ButtonStyle::DangerOutline;
    });
```

The context carries the model of the row the action belongs to, the request, the config, and whether the action is
being rendered inside a dropdown or as a bulk action. A table action and a bulk action have no row, so `model` is
`null` there. Use `?->` as above rather than guarding.

The closure runs once per render, so the same action definition can style each row differently.

Static styles and whatever the closure returns are all applied, in the order you pass them; a closure declared first
still contributes after the static cases. Nothing is resolved or de-duplicated: if you declare two styles that fight,
both classes are emitted and CSS decides.

```php
<?php

new Action()->style(
    ButtonStyle::Danger,
    fn (ActionContext $context) => $context->isBulk ? ButtonStyle::Link : null,
);
```

:::note

The dropdown rule still applies to a closure. A case returned for an action inside a dropdown renders as its colour
only, so the example above gives `text-danger` there and `btn-danger` everywhere else.

:::

## Styling a dropdown

An [action collection](../actions/action-collections.md) of the dropdown type has a toggle button of its own, and it
takes the same `style()` method:

```php
<?php

use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;

new ActionCollection()
    ->dropdown($edit, $delete)
    ->label(__('Actions'))
    ->style(ButtonStyle::SecondaryOutline);
```

The toggle is a button that sits outside the menu, so it renders the button variant even though it opens a dropdown.
The actions inside the menu are unaffected by it and keep their own styles.

Only the dropdown type has something to style. A style declared on a normal or a grouped collection is accepted and
does nothing, because those render a plain wrapper rather than a button.

:::note

A collection loses its style and its type when you transform it, because `filter()` and `map()` build a plain
collection. Declare the style after transforming, not before.

:::
