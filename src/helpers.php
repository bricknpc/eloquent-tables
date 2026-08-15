<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables;

use BrickNPC\EloquentTables\Actions\Action;
use BrickNPC\EloquentTables\Enums\ActionCollectionType;
use BrickNPC\EloquentTables\Actions\Collections\ActionCollection;
use BrickNPC\EloquentTables\Enums\Theme;

function actions(Action|ActionCollection ...$items): ActionCollection
{
    return new ActionCollection($items);
}

function dropdownActions(Action|ActionCollection ...$items): ActionCollection
{
    return new ActionCollection($items, ActionCollectionType::Dropdown);
}

function groupedActions(Action|ActionCollection ...$items): ActionCollection
{
    return new ActionCollection($items, ActionCollectionType::Grouped);
}

function view(Theme $theme, string $view): string
{
    return 'eloquent-tables::' . $theme->value . '.' . $view;
}
