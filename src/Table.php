<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use Illuminate\Http\Request;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\PageStyle;
use BrickNPC\EloquentTables\Enums\TableStyle;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Translation\Translator;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Builders\TableViewBuilder;
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
     * @var TableViewBuilder<TModel>
     */
    public TableViewBuilder $builder {
        set(TableViewBuilder $value) {
            $this->builder = $value;
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

        return $this->builder->build($this, $this->request);
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
     * @return TableStyle[]
     */
    public function tableStyles(): array
    {
        return [
            TableStyle::Default,
        ];
    }

    public function pageStyle(): PageStyle
    {
        return PageStyle::Primary;
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
