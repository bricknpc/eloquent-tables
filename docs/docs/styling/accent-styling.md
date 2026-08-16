---
sidebar_position: 5
---

# Accent styling

A table renders more than rows: a header with a search box and filters, and pagination links underneath. The accent is
the colour those controls are drawn in, so they sit together with the table rather than clashing with it.

It affects the search box, the filter dropdowns, the per-page dropdown and the pagination links.

Declare it with `accentStyle()`, returning a `StyleSet` over `BrickNPC\EloquentTables\Enums\AccentStyle`:

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\Enums\AccentStyle;

class UserTable extends Table
{
    //... Other methods

    public function accentStyle(): ?StyleSet
    {
        return new StyleSet(AccentStyle::Success);
    }
}
```

The default is `AccentStyle::Primary`.

## One accent, not a set

Every other styling level can carry several styles at once. The accent cannot — it is a single colour, used to build
several classes. If you declare more than one, the last wins:

```php
new StyleSet(AccentStyle::Danger, AccentStyle::Success); // success
```

A closure works the same way as it does elsewhere, and receives a
`BrickNPC\EloquentTables\Styles\Contexts\TableContext`:

```php
new StyleSet(fn (TableContext $context) => $context->request->user()?->prefersDarkMode
    ? AccentStyle::Dark
    : AccentStyle::Primary);
```

:::note
This was `pageStyle()` returning a `PageStyle` in 1.x. It was renamed because it never governed a page — it governs the
table's own controls. See [Upgrading](../upgrading.md).
:::
