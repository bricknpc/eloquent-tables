<?php

declare(strict_types=1);

/*
 * Eloquent Tables Configuration
 * ================================
 *
 * Configuration options for the Eloquent Tables package.
 *
 * A note on the query name options below, because it changed in 2.0. Every table namespaces the
 * request data it reads under its own name, so a name configured here is a key *inside* that
 * namespace rather than a top-level query parameter. With the defaults, a table named "user" reads:
 *
 *     ?user[search]=ada&user[sort][email]=asc&user[filter][active]=1&user[page]=2&user[per_page]=50
 *
 * Changing 'search' to 'q' below makes that ?user[q]=ada. The table name itself comes from the
 * table class, or from whatever its name() method returns, and is not configurable here.
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
         * The key holding the search term, read as ?{table}[search]=ada.
         */
        'query_name' => 'search',
    ],

    /*
     * Sorting options
     * --------------------------------
     * Sorting is automatically enabled when one or more columns on a table are marked as sortable.
     */
    'sorting' => [
        /*
         * The key holding the sort, read as ?{table}[sort][email]=asc. It is an array keyed by column name
         * with the direction (asc or desc) as the value, and its order is the order the visitor clicked the
         * headers, which is the order the sort is applied in.
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
         * The key holding the filters, read as ?{table}[filter][active]=1. It is an array keyed by filter
         * name with the filter value as the value.
         */
        'query_name' => 'filter',
    ],

    /*
     * Pagination options
     * --------------------------------
     * Pagination is enabled by adding the WithPagination trait to a table.
     */
    'pagination' => [
        /*
         * The key holding the current page, read as ?{table}[page]=2. Note that this is nested under the
         * table name like every other key here, so it is not Laravel's usual top-level ?page=2.
         */
        'page_query_name' => 'page',

        /*
         * The key holding the number of items per page, read as ?{table}[per_page]=50.
         */
        'per_page_query_name' => 'per_page',
    ],

    /*
     * Preferences
     * --------------------------------
     * A table remembers the number of items per page and the sort a visitor chose, so the choice survives
     * navigating away and back. Both are stored in one cookie on the visitor's own device, as JSON keyed by
     * table name, and nothing is written to your database. The cookie is set by the bundled JavaScript and
     * read back server-side, so like any cookie it is sent with every request to your application.
     *
     * Disable this if you would rather not store anything on the visitor's device. Tables keep working; they
     * just start from their defaults on every visit.
     */
    'preferences' => [
        /*
         * Whether a table stores the visitor's choices at all.
         */
        'enabled' => true,

        /*
         * The name of the cookie every table's preferences are stored in.
         *
         * This name is registered with EncryptCookies::except(), because the JavaScript has to read the
         * cookie before the table renders and cannot decrypt it. Change the name and the exemption follows
         * it. Keep the cookie to the visitor's own display choices, which is all the package puts there, and
         * never anything you would mind being readable.
         */
        'cookie_name' => 'eloquent_tables_preferences',
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
        'search'   => new HtmlString('&#x1F50E;&#xFE0E;'),
        'sort-asc' => '⭡', // new HtmlString("&#x25B2;"), // "u\{25B2}"
        'sort-desc' => '⭣', // new HtmlString("&#x25BC;"), // "u\{25BC}"
        'sort-none' => '⭥', // new HtmlString("&#x25C0;"), // "u\{25C0}"
        'check' => '✓', // new HtmlString("&check;"), // "u\{2713}"
        'cross' => '✗', // new HtmlString("&cross;"), // "u\{2717}"
    ],

    /*
     * Tables location
     * --------------------------------
     *
     * The location where the generated tables will be created.
     */
    'tables-location' => 'Tables',
];
