---
sidebar_position: 14.5
---

# Table names

Every table has a name. It is what separates one table's query parameters from another's, and what its saved
preferences are stored against.

You get one without doing anything: the name is derived from the class name, with a trailing `Table` removed and the
rest snake-cased.

| Class                | Name             |
|----------------------|------------------|
| `UserTable`          | `user`           |
| `ArchivedUserTable`  | `archived_user`  |
| `Invoices`           | `invoices`       |

## Where the name shows up

In the query string. Everything a table reads is nested under its name, so a page can render as many tables as it likes
without them reading each other's values:

```
?user[search]=ada&user[sort][email]=asc&invoices[page]=2
```

## Choosing your own

Override `name()` to pick something else. The name appears in URLs your users see and share, so a short, readable one
is worth choosing:

```php
<?php
// app/Tables/ArchivedUserTable.php
declare(strict_types=1);

namespace App\Tables;

use BrickNPC\EloquentTables\Table;

class ArchivedUserTable extends Table
{
    public function name(): string
    {
        return 'archived';
    }

    //... Table definition
}
```

Changing a name changes the URLs for that table, and orphans any preference a visitor had saved under the old one, so
their next visit starts from your defaults. It is worth settling on a name before you ship.

## Two tables of the same class

Two instances of the same table class resolve to the same name, so they share one set of query parameters and one saved
preference. Sorting one sorts both.

That is occasionally what you want. When it is not, give at least one of them its own name:

```php
<?php

$active   = new UserTable();          // name: "user"
$archived = new ArchivedUserTable();  // name: "archived"
```

:::warning
The bundled JavaScript warns in the browser console when it finds two tables on a page sharing a name. If a table is
mysteriously reacting to another table's controls, check the console first.
:::

## Naming rules

There are none enforced, but the name ends up in a query string, so:

- Stick to characters that survive a URL without encoding: letters, digits, `_` and `-`.
- Avoid names that collide with query parameters your application already uses on the same page.
