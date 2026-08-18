<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Filters;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Contracts\Filter;
use BrickNPC\EloquentTables\Enums\TableParameter;
use BrickNPC\EloquentTables\Services\TableParameters;

/**
 * @template TModel of Model
 */
readonly class FilterRenderer
{
    /**
     * @param TableParameters<TModel> $parameters
     */
    public function __construct(
        private Factory $viewFactory,
        private Config $config,
        private TableParameters $parameters,
    ) {}

    /**
     * @param Table<TModel> $table
     */
    public function build(Filter $filter, Table $table, Request $request): View
    {
        $values = $this->parameters->arrayValue($table, TableParameter::Filter, $request);

        $theme = $this->config->theme();

        return $this->viewFactory->make($filter->view(), [
            'theme' => $theme,
            // The view is rendered on its own rather than included, so it inherits nothing from the
            // table's scope and needs the style passed explicitly.
            'mainTableStyle' => $table->accentStyle()->toCssClass($theme),
            'options'        => $filter->options(),
            'name'           => $filter->name,
            'value'          => $values[$filter->name] ?? null,
            'action'         => $request->fullUrl(),
            // The view composes "{queryName}[{name}]", so handing it the table's nested filter key
            // yields users[filter][active] rather than a page-wide filter[active].
            'queryName'    => $this->parameters->key($table, TableParameter::Filter),
            'hiddenInputs' => $this->parameters->hiddenInputs($request, [
                // Only this filter's own key: the table's other filters must survive the submit.
                $this->parameters->key($table, TableParameter::Filter) . '[' . $filter->name . ']',
                $this->parameters->key($table, TableParameter::Page),
            ]),
        ]);
    }
}
