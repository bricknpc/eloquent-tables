---
sidebar_position: 8
---

# Authorisation

Sometimes you want to check if a user is allowed to view a Table. This is especially the case when using the Table as a 
controller. You can implement the `authorize` method on your table. This method receives the current request so it can 
perform authorisation. The method must return a `boolean` value indicating whether the current user can view the table.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;

class UserTable extends \Relay\Table
{
    //... Other methods
    
    protected function authorize(Request $request): bool
    {
        return $request->user()->can('viewAny', User::class);
    }
}
```

## Customising the error message

You can customise the error message shown when a user is not authorised to view the table by implementing the 
`unauthorizedMessage` method on your table.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;

class UserTable extends \Relay\Table
{
    //... Other methods
    
    protected function unauthorizedMessage(): string
    {
        return __('You don\'t have permission to view the user table.');
    }
}
```

## Customising the error code

You can customise the error code used when a user is not authorised to view the table by implementing the
`unauthorizedResponseCode` method on your table.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;
use Symfony\Component\HttpFoundation\Response;

class UserTable extends \Relay\Table
{
    //... Other methods
    
    protected function unauthorizedResponseCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
```

## Customising the unauthorized event

You can customise the entire unauthorised scenario by implementing the `unauthorized` method on your table.

:::warning[Note]

The `unauthorized` method **MUST** throw an exception. If not, the table is rendered as if the user has access to the
table, even if the `authorize` method returned `false`.

:::

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminte\Http\Request;
use BrickNPC\EloquentTables\Table;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserTable extends \Relay\Table
{
    //... Other methods
    
    protected function unauthorized(): void
    {
        throw new HttpException(
            statusCode: $this->unauthorizedResponseCode(),
            message: $this->unauthorizedMessage(),
        );
    }
}
```
