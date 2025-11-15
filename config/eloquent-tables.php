<?php

declare(strict_types=1);

/*
 * Eloquent Tables Configuration
 * ================================
 *
 * Configuration options for the Eloquent Tables package.
 */

use BrickNPC\EloquentTables\Enums\Theme;

return [
    /*
     * Theme
     * --------------------------------
     * The theme to use for rendering the tables. Use any of the values from the Theme enum.
     */
    'theme' => Theme::Bootstrap5,

    /*
     * Search options
     * --------------------------------
     * Searching is automatically enabled when one or more columns on a table are marked as searchable.
     */
    'search' => [
        /*
         * The name of the query parameter used for searching.
         */
        'query_name' => 'search',
    ],

    /*
     * Sorting options
     * --------------------------------
     * Searching is automatically enabled when one or more columns on a table are marked as sortable.
     */
    'sorting' => [
        /*
         * The name of the query parameter used for sorting. The sort query parameter is an array where the keys are
         * the names of the columns to sort by and the values are the sort directions (asc or desc).
         */
        'query_name' => 'sort',
    ],
];
