# Authorization

You can check if a user has access to the table by implementing the `authorize` method on your table.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;

class UserTable extends Table
{
    protected function authorize(Request $request): bool
    {
        return $request->user()->can('viewAny', User::class);
    }
}
```

If the user is not authorized to view the table (when the `authorize` method returns `false`), the table 
`unauthorized` callback will be called. You can customise the HTTP response code returned by this method and 
the message displayed to the user by implementing the `unauthorizedMessage` and `unauthorizedResponseCode` methods on 
your table.

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;
use BrickNPC\EloquentTables\Table;
use Symfony\Component\HttpFoundation\Response;

class UserTable extends Table
{
    protected function unauthorizedMessage(): string
    {
        return $this->trans->get('You are not authorized to view this table.');
    }
    
    protected function unauthorizedResponseCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
```

You can also overwrite the `unauthorized` callback to return a custom response.

> Note: The `unauthorized` method **MUST** throw an exception. If not, the table will be rendered as if the user has 
> access. 

```php
<?php
// app/Tables/UserTable.php

declare(strict_types=1);

namespace App\Tables;

use App\Models\User;

use BrickNPC\EloquentTables\Table;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserTable extends Table
{
    /**
     * @throws HttpException
     */
    protected function unauthorized(): void
    {
        throw new HttpException(
            statusCode: $this->unauthorizedResponseCode(), 
            message: $this->unauthorizedMessage(), 
        );
    }
}
```