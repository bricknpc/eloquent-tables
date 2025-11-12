# Themes

BrickNPC Eloquent Tables support themes based on the frontend framework of your choice. You can, of course, create 
custom CSS code if you don't use any of the supported frameworks.

## Setting a theme

To set a theme, publish the config file and set the `theme` key to the name of the theme you want to use.

```php
// config/eloquent-tables.php

return [
    'theme' => \BrickNPC\EloquentTables\Enums\Theme::Bootstrap5,
];
```

## Supported themes

- Bootstrap 5