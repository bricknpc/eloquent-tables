<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use BrickNPC\EloquentTables\Column;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use BrickNPC\EloquentTables\Enums\CellStyle;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Enums\TableStyle;
use BrickNPC\EloquentTables\Contracts\Formatter;
use BrickNPC\EloquentTables\Factories\FormatterFactory;

/**
 * @template TModel of Model
 */
readonly class ColumnValueViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private FormatterFactory $formatterFactory,
        private Config $config,
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
                : $this->formatterFactory->build($column->formatter, $column->getFormatterParameters());

            $value = $formatter->format($value, $model);
        }

        return $this->viewFactory->make('eloquent-tables::table.td', [
            'theme'          => $theme,
            'value'          => $value,
            'styles'         => collect($column->styles)->map(fn (TableStyle $style) => $style->toCssClass($theme))->implode(' '),
            'cellStylesFlex' => collect($column->cellStyles)->map(fn (CellStyle $style) => $style->toCssClass($theme, true))->implode(' '),
            'cellStyles'     => collect($column->cellStyles)->map(fn (CellStyle $style) => $style->toCssClass($theme, false))->implode(' '),
            'type'           => $column->type,
            'checkIcon'      => $this->config->checkIcon(),
            'crossIcon'      => $this->config->crossIcon(),
        ]);
    }
}
