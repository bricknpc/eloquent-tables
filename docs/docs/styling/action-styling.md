---
sidebar_position: 2
---

# Action Styling

Action styles define the look and feel of actions. They add styling to your buttons and links based on the current theme 
(for now only Bootstrap 5).

You apply them with the `BrickNPC\EloquentTables\Actions\Capabilities\Style` capability, which accepts one or more
`BrickNPC\EloquentTables\Enums\ButtonStyle` enum cases. See the
[action definition](../actions/action-definition.md#style) documentation for more details on capabilities.

```php
<?php

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\Capabilities\Style;

new Action()
    ->label(__('Delete'))
    ->as(...)
    ->with(new Style(ButtonStyle::DangerOutline));
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

An action without the Style capability is rendered with the `btn-primary` class, so every action looks the same until
you say otherwise.

## Actions inside a dropdown

An action inside an [action collection](../actions/action-collections.md) of the dropdown type is rendered as a menu
item instead of a button. A button style would look out of place there, so only the colour of the style is used:
`ButtonStyle::Danger` and `ButtonStyle::DangerOutline` both render as `text-danger` inside a dropdown.

## Combining styles

The Style capability accepts more than one style. They are rendered in the order you pass them.

```php
<?php

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ButtonStyle;
use BrickNPC\EloquentTables\Actions\Capabilities\Style;

new Action()->with(new Style(ButtonStyle::Danger, ButtonStyle::Link));
```
