<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

/**
 * The query parameters a table owns.
 *
 * Each one is nested under the table's name, so two tables on a page never read each other's values.
 * The sub-key each case resolves to is configurable; see Services\TableParameters.
 */
enum TableParameter
{
    case Search;
    case Sort;
    case Filter;
    case PerPage;
    case Page;
}
