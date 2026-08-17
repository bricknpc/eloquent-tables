<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum Theme: string
{
    case Bootstrap5 = 'bootstrap-5';

    public function view(string $view): string
    {
        return 'eloquent-tables::' . $this->value . '.' . $view;
    }

    public function getLinksView(): string
    {
        return $this->view('pagination');
    }
}
