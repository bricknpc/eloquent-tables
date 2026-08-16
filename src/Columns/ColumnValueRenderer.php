<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Columns;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\StyleTarget;
use BrickNPC\EloquentTables\Contracts\Formatter;
use BrickNPC\EloquentTables\Styles\StyleResolver;
use BrickNPC\EloquentTables\Factories\FormatterFactory;
use BrickNPC\EloquentTables\Styles\Contexts\CellContext;

/**
 * @template TModel of Model
 */
readonly class ColumnValueRenderer
{
    public function __construct(
        private Factory $viewFactory,
        private FormatterFactory $formatterFactory,
        private Config $config,
        private StyleResolver $styleResolver,
    ) {}

    /**
     * @param Column<TModel> $column
     * @param TModel         $model
     */
    public function build(Request $request, Column $column, Model $model): View
    {
        $theme = $this->config->theme();

        $value = is_callable($column->valueUsing) ? call_user_func($column->valueUsing, $model) : $model->{$column->name};

        if ($column->formatter !== null) {
            $formatter = $column->formatter instanceof Formatter
                ? $column->formatter
                : $this->formatterFactory->build(
                    $column->formatter,
                    $this->resolveFormatterParameters($column->getFormatterParameters(), $model),
                );

            $value = $formatter->format($value, $model);
        }

        $styles = $column->style?->resolve(new CellContext($request, $column, $model)) ?? [];

        return $this->viewFactory->make('eloquent-tables::table.td', [
            'theme'          => $theme,
            'value'          => $value,
            'styles'         => $this->styleResolver->classes($styles, StyleTarget::Cell),
            'cellStylesFlex' => $this->styleResolver->classes($styles, StyleTarget::Content, true),
            'cellStyles'     => $this->styleResolver->classes($styles, StyleTarget::Content),
            'type'           => $column->type,
            'checkIcon'      => $this->config->checkIcon(),
            'crossIcon'      => $this->config->crossIcon(),
        ]);
    }

    /**
     * Resolves any formatter parameter that was given as a closure, handing it the model of the current row.
     *
     * @param array<string, mixed> $parameters
     * @param TModel               $model
     *
     * @return array<string, mixed>
     */
    private function resolveFormatterParameters(array $parameters, Model $model): array
    {
        return array_map(
            fn (mixed $parameter): mixed => $parameter instanceof \Closure
                ? call_user_func($parameter, $model)
                : $parameter,
            $parameters,
        );
    }
}
