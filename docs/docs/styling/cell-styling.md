---
sidebar_position: 4
---

# Cell styling

Cell styles control how a column's cells look and how their content sits. They apply to the header and to every body
cell of that column.

Declare them with `style()` on a column:

```php
<?php

use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\CellStyle;

new Column('total')->style(CellStyle::AlignRight, CellStyle::FontBold);
```

## What you can say

| Family | Cases | Renders as |
|---|---|---|
| Alignment | `AlignLeft`, `AlignCenter`, `AlignRight`, `AlignJustify`, `AlignBetween`, `AlignTop`, `AlignMiddle`, `AlignBottom` | flex alignment on the cell's content |
| Background | `BackgroundPrimary` to `BackgroundDark` | a contextual fill on the cell itself |
| Text colour | `TextPrimary` to `TextDark` | a colour on the cell's text |
| Weight | `FontLight`, `FontNormal`, `FontSemibold`, `FontBold` | a font weight on the cell |

Backgrounds and text colours cover the same ten colours the [table styles](table-styling.md) offer.

Each case knows where it belongs. Alignment is applied to the content inside the cell; everything else is applied to
the cell itself, so a background fills the whole cell including its padding.

## Styling a cell by its value

Pass a closure alongside the static styles. It receives a
`BrickNPC\EloquentTables\Styles\Contexts\CellContext` and returns a case, a list of cases, or `null`:

```php
<?php

use BrickNPC\EloquentTables\Column;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Styles\Contexts\CellContext;

new Column('total_amount')->style(
    CellStyle::AlignRight,
    function (CellContext $context): ?CellStyle {
        if ($context->model?->total_amount < 0) {
            return CellStyle::TextDanger;
        }

        if ($context->model?->total_amount < 10) {
            return CellStyle::TextWarning;
        }

        return null;
    },
);
```

Here every cell is right-aligned, and negative amounts are additionally red.

The context carries the column, the model, and which part of the table is being rendered. The closure runs for the
header too, where there is no model, which is why the example above returns `null` for it without needing a guard.

[Action styling](action-styling.md#styling-an-action-by-its-row) takes the same shape, with `ButtonStyle` cases and an
`ActionContext`.

## Styles merge; they are not resolved

Static styles and whatever the closure returns are all applied. If you declare two that fight (a success background
statically and a danger background conditionally), both classes are emitted and CSS decides. That is deliberate: the
package does not guess which one you meant.

## Column types bring their own defaults

A `boolean()` or `checkbox()` column centres its content unless you say otherwise. A default only applies where you
declared nothing in the same family, so:

```php
new Column('active')->boolean();                              // centred
new Column('active')->boolean()->style(CellStyle::AlignLeft); // left, not centred
new Column('active')->boolean()->style(CellStyle::FontBold);  // still centred, and bold
```

See [column types](column-types.md).
