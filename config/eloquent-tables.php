<?php

declare(strict_types=1);

/*
 * Eloquent Tables Configuration
 * ================================
 *
 * Configuration options for the Eloquent Tables package.
 */

use Illuminate\Support\HtmlString;
use BrickNPC\EloquentTables\Enums\Theme;

return [
    /*
     * Theme
     * --------------------------------
     * The theme to use for rendering the tables. Use any of the values from the Theme enum.
     */
    'theme' => Theme::Bootstrap5,

    /*
     * Data namespace
     * --------------------------------
     * Eloquent Tables uses data attributes to store information about the table state. This option allows you to use
     * a different namespace for these attributes to avoid conflicts with other libraries or your own custom attributes.
     */
    'data-namespace' => 'et',

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

    /*
     * Filtering options
     * --------------------------------
     * Filtering is automatically enabled when one or more filters are defined on a table.
     */
    'filtering' => [
        /*
         * The name of the query parameter used for filtering. The filter query parameter is an array where the keys are
         * the names of the filters and the values are the filter values.
         */
        'query_name' => 'filter',
    ],

    /*
     * Icons
     * --------------------------------
     * This package shows icons in various places. You can customise the icons shown here.
     *
     * The icons defined here should be either a string or a Stringable object. When using HTML encoded strings, wrap
     * them in a HtmlString object.
     */
    'icons' => [
        'search'    => new HtmlString('&#x1F50E;&#xFE0E;'),
        'sort-asc'  => '⭡', // new HtmlString("&#x25B2;"), // "u\{25B2}"
        'sort-desc' => '⭣', // new HtmlString("&#x25BC;"), // "u\{25BC}"
        'sort-none' => '⭥', // new HtmlString("&#x25C0;"), // "u\{25C0}"
    ],
];
