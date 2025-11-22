---
sidebar_position: 2
---

# Configuration

The Eloquent Tables package provides several configuration options. To change these options, you should publish
the config file.

## Options

### Theme

The `theme` option tells the package which frontend framework is used. This must be one of the 
`BrickNPC\EloquentTables\Enums\Theme` enum options. For now, only Bootstrap 5 is supported.

```php
<?php
// config/eloquent-tables.php

return [
    // Other options
    'theme' => BrickNPC\EloquentTables\Enums\Theme::Bootstrap5,
];
```

### Data namespace

The Eloquent Tables package uses HTML `data-` attributes to store data inside HTML attributes. This data is used by the 
JavaScript of the package. To avoid collisions with other libraries or your own data attributes, Eloquent Tables uses a 
namespace for these data attributes. Configure this namespace here.

```php
<?php
// config/eloquent-tables.php

return [
    // Other options
    'data-namespace' => 'et',
];
```

### Search

When you add the `searchable` option to a column in your table, the table automatically displays a search bar at the top 
of the table. The `search` config option defines configuration options for this search bar.

#### Query name

The `query_name` search option defines the name of query parameter that is used for the search value.

```php
<?php
// config/eloquent-tables.php

return [
    // Other options
    'search' => [
        'query_name' => 'search'
    ],
];
```

### Sorting

When you add the `sortable` option to a column in your table, the table can be sorted by that column. The `sorting` 
config option defines configuration options for this behavior.

#### Query name

The `query_name` sorting option defines the name of query parameter that is used for the sorting value. Sorting a table 
is done by adding this query parameter to the URL as an array, where the keys are the column names and the values are 
the sorting direction. This way you can sort on multiple columns.

```php
<?php
// config/eloquent-tables.php

return [
    // Other options
    'sorting' => [
        'query_name' => 'sort'
    ],
];
```

### Filtering

When you define filters on your table, the table can be filtered based on those filters. The `filtering` config option 
defines configuration options for this behavior.

#### Query name

The `query_name` filtering option defines the name of query parameter that is used for the filtering value. Filtering a 
table is done by adding the filter query parameter to the URL as an array, where the keys are the names of the filter, 
and the values are the filter values. This way you can use multiple filters.

```php
<?php
// config/eloquent-tables.php

return [
    // Other options
    'filtering' => [
        'query_name' => 'filter'
    ],
];
```

### Icons

The Eloquent Tables package uses icons in a few places. To keep the icons consistent with your own style and icon 
library, you can configure the icons in the config. By default, the package uses UTF-8 characters or HTML Encoded 
entities.

These are the icons that are available:

| Icon name   | Usage                                                            |
|-------------|------------------------------------------------------------------|
| `search`    | The search icon in the search bar                                |
| `sort-asc`  | The icon for sorting a table in ascending order                  |
| `sort-desc` | The icon for sorting a table in descending order                 |
| `sort-none` | The icon for when a column is sortable, but currently not sorted |
| `check`     | A checkmark used in boolean and checkmark type columns           |
| `cross`     | A cross used in boolean and checkmark type columns.              |

```php
<?php
// config/eloquent-tables.php

return [
    // Other options
    'icons' => [
        'search'    => new HtmlString('&#x1F50E;&#xFE0E;'),
        'sort-asc'  => '⭡',
        'sort-desc' => '⭣',
        'sort-none' => '⭥',
        'check'     => '✓',
        'cross'     => '✗',
    ],
];
```

## App configuration

Because the Eloquent Tables package is a Laravel package, there are a few Laravel configuration options that this 
package uses as well.

### Locale

The built-in formatters need a locale to format things like dates and numbers. The Eloquent Tables package uses the 
current locale of the application, which is usually set in the `.env` file as `APP_LOCALE`.

### Timezone

The built-in date formatters also need a timezone to format dates. The Eloquent Tables package uses the current 
timezone of the application, which is usually set in the `app.php` config file under the Application Timezone heading.

### Decimals

This option is not a default Laravel configuration option, so you should add it yourself. The Eloquent Tables package 
uses this option to format numbers. It is not required to add this option, but it is recommended to do so. Add a 
`decimals` option to the `app.php` config file and set it to the default number of decimals you want to use when 
formatting numbers.

### Currency

This option is not a default Laravel configuration option, so you should add it yourself. The Eloquent Tables package 
uses this option to format currency. It is not required to add this option, but it is recommended to do so. 
Add a `currency` option to the `app.php` config file and set it to the currency you want to use when formatting 
numbers as currency. The value should be a 3-letter ISO 4217 currency code indicating the currency to use.