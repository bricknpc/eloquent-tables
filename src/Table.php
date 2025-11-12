<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use Illuminate\Http\Request;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Illuminate\Contracts\View\View;
use BrickNPC\EloquentTables\Enums\Theme;
use Illuminate\Contracts\Config\Repository;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class Table implements LoggerAwareInterface, \Stringable
{
    use LoggerAwareTrait;

    public Request $request {
        set(Request $value) {
            $this->request = $value;
        }
    }

    public ViewFactory $viewFactory {
        set(ViewFactory $value) {
            $this->viewFactory = $value;
        }
    }

    public Translator $trans {
        set(Translator $value) {
            $this->trans = $value;
        }
    }

    public Repository $config {
        set(Repository $value) {
            $this->config = $value;
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

    abstract public function query(): Builder;

    /**
     * @return Column[]
     */
    abstract public function columns(): array;

    public function render(): View
    {
        if (!$this->authorize($this->request)) {
            $this->unauthorized();
        }

        return $this->viewFactory->make('eloquent-tables::table', [
            'theme' => Theme::from($this->config->get('eloquent-tables.theme')),
        ]);
    }

    public function filters(): array
    {
        return [];
    }

    /*
     * These functions are supposed to be overwritten by the user, but they are not required or have some default
     * behaviour. That is why they are not marked as abstract.
     */

    /**
     * Check whether the current user is authorized to view the table.
     */
    protected function authorize(Request $request): bool
    {
        return true;
    }

    protected function unauthorizedMessage(): string
    {
        return $this->trans->get('You are not authorized to view this table.');
    }

    protected function unauthorizedResponseCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }

    /**
     * Unauthorized callback.
     *
     * This callback is executed when the user is not authorized to view the table. THis method must always throw an
     * exception, otherwise the table is rendered as normal even when the user is not authorized.
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
