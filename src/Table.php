<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use Illuminate\Http\Request;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Enums\TableStyle;
use BrickNPC\EloquentTables\Actions\RowAction;
use Symfony\Component\HttpFoundation\Response;
use BrickNPC\EloquentTables\Actions\MassAction;
use BrickNPC\EloquentTables\Actions\TableAction;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Translation\Translator;
use BrickNPC\EloquentTables\Concerns\WithPagination;
use BrickNPC\EloquentTables\Builders\TableViewBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    public TableViewBuilder $builder {
        set(TableViewBuilder $value) {
            $this->builder = $value;
        }
    }

    public function __invoke(): View
    {
        return $this->render();
    }

    public function __toString(): string
    {
        return $this->render()->render();
    }

    public function render(): View
    {
        if (!$this->authorize($this->request)) {
            $this->unauthorized();
        }

        return $this->builder->build($this, $this->request);
    }

    public function withPagination(): bool
    {
        return in_array(WithPagination::class, class_uses_recursive(static::class), true);
    }

    abstract public function query(): Builder;

    /**
     * @return Column[]
     */
    abstract public function columns(): array;

    /*
     * These functions are supposed to be overwritten by the user, but they are not required or have some default
     * behaviour. That is why they are not marked as abstract.
     */

    //    public function filters(): array
    //    {
    //        return [];
    //    }

    /**
     * @return TableStyle[]
     */
    public function tableStyles(): array
    {
        return [
            TableStyle::Default,
        ];
    }

    /**
     * @return TableAction[]
     */
    public function tableActions(): array
    {
        return [];
    }

    /**
     * @return RowAction[]
     */
    public function rowActions(): array
    {
        return [];
    }

    /**
     * @return MassAction[]
     */
    public function massActions(): array
    {
        return [];
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
