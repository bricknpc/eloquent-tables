<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum TableParameter
{
    case Search;
    case Sort;
    case Filter;
    case PerPage;
    case Page;
}
