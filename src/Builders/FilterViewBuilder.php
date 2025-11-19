<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Builders;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Contracts\View\Factory;
use BrickNPC\EloquentTables\Services\Config;
use BrickNPC\EloquentTables\Contracts\Filter;

readonly class FilterViewBuilder
{
    public function __construct(
        private Factory $viewFactory,
        private Config $config,
    ) {}

    public function build(Filter $filter, Request $request): View
    {
        return $this->viewFactory->make($filter->view(), [
            'theme'     => $this->config->theme(),
            'options'   => $filter->options(),
            'name'      => $filter->name,
            'value'     => isset($request->query($this->config->filterQueryName(), [])[$filter->name]) ? $request->query($this->config->filterQueryName(), [])[$filter->name] : null,
            'action'    => $request->fullUrl(),
            'queryName' => $this->config->filterQueryName(),
        ]);
    }
}
