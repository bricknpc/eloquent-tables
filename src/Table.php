<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\TableStyle;
use BrickNPC\EloquentTables\Enums\AccentStyle;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Translation\Translator;
use BrickNPC\EloquentTables\Tables\TableRenderer;
use BrickNPC\EloquentTables\ValueObjects\StyleSet;
use BrickNPC\EloquentTables\ValueObjects\FooterRow;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpException;
use BrickNPC\EloquentTables\Exceptions\MissingMethodException;

/**
 * @template TModel of Model
 */
abstract class Table implements LoggerAwareInterface, \Stringable
{
    use LoggerAwareTrait;

    public Request $request {
        set(Request $value) {
            $this->request = $value;
        }
    }

    public Translator $trans {
        set(Translator $value) {
            $this->trans = $value;
        }
    }

    /**
     * @var TableRenderer<TModel>
     */
    public TableRenderer $renderer {
        // @mago-expect analysis:invalid-property-assignment-value
        set(TableRenderer $value) {
            $this->renderer = $value;
        }
    }

    /**
     * @throws MissingMethodException
     * @throws HttpException
     */
    public function __invoke(): View
    {
        return $this->render();
    }

    /**
     * @throws MissingMethodException
     * @throws HttpException
     */
    public function __toString(): string
    {
        return $this->render()->render();
    }

    /**
     * @throws MissingMethodException
     * @throws HttpException
     */
    public function render(): View
    {
        if (!method_exists($this, 'query')) {
            throw MissingMethodException::forMethod('query');
        }

        if (!method_exists($this, 'columns')) {
            throw MissingMethodException::forMethod('columns');
        }

        if (!$this->authorize($this->request)) {
            $this->unauthorized();
        }

        return $this->renderer->build($this, $this->request);
    }

    public function withPagination(): bool
    {
        return in_array(WithPagination::class, class_uses_recursive(static::class), true);
    }

    public function hasFilters(): bool
    {
        return method_exists($this, 'filters');
    }

    /**
     * The table's stable name, used to namespace its query parameters and its stored preferences.
     *
     * The name is derived from the class name with a trailing "Table" removed, so UserTable becomes
     * "user". Two instances of the same table class share a name, and therefore share both
     * namespaces; override this to keep them independent.
     */
    public function name(): string
    {
        // PHP names an anonymous class "<parent>@anonymous<NUL><file>:<line>$<hash>", so cut it back
        // to the parent before taking the basename.
        $basename = class_basename(Str::before(static::class, '@anonymous'));

        $stripped = str_ends_with($basename, 'Table')
            ? substr($basename, 0, -strlen('Table'))
            : $basename;

        return Str::snake($stripped === '' ? $basename : $stripped);
    }

    public function rowStyle(): ?StyleSet
    {
        return null;
    }

    /**
     * @return TableStyle[]
     */
    public function style(): array
    {
        return [TableStyle::Default];
    }

    public function accentStyle(): AccentStyle
    {
        return AccentStyle::Primary;
    }

    /**
     * @return FooterRow[]
     */
    public function footer(): array
    {
        return [];
    }

    /**
     * The width of the leading column holding the bulk action checkboxes, as a CSS length.
     *
     * Return null to omit the inline width entirely and size the column from your own stylesheet.
     */
    public function bulkActionColumnWidth(): ?string
    {
        return '5%';
    }

    /**
     * Check whether the current user is authorised to view the table.
     */
    protected function authorize(Request $request): bool
    {
        return true;
    }

    protected function unauthorizedMessage(): string
    {
        // @mago-expect analysis:mixed-return-statement
        // @todo Create own wrapper around the Laravel translator so we can ensure type-safety
        return $this->trans->get('You are not authorized to view this table.'); // @phpstan-ignore-line
    }

    protected function unauthorizedResponseCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    /**
     * Unauthorised callback.
     *
     * This callback is executed when the user is not authorised to view the table. THis method must always throw an
     * exception, otherwise the table is rendered as normal even when the user is not authorised.
     *
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
