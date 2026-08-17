---
sidebar_position: 5
---

# Accent styling

A table renders more than rows: a header with a search box and filters, and pagination links underneath. The accent is
the colour those controls are drawn in, so they sit together with the table rather than clashing with it.

It affects the search box, the filter dropdowns, the per-page dropdown and the pagination links.

Declare it with `accentStyle()`, returning a `BrickNPC\EloquentTables\Enums\AccentStyle` case:

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Enums\AccentStyle;

class UserTable extends Table
{
    //... Other methods

    public function accentStyle(): AccentStyle
    {
        return AccentStyle::Success;
    }
}
```

The default is `AccentStyle::Primary`.

## Varying it per request

The accent is a single colour, so the method returns one case rather than a set. A table already has the request
injected, so anything conditional belongs in the method body:

```php
public function accentStyle(): AccentStyle
{
    return $this->request->user()?->prefersDarkMode ? AccentStyle::Dark : AccentStyle::Primary;
}
```

:::note
This was `pageStyle()` returning a `PageStyle` in 1.x. It was renamed because it never governed a page. It governs the
table's own controls. See [Upgrading](../upgrading.md).
:::
