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
    'theme' => Theme::Bootstrap5->value,
];