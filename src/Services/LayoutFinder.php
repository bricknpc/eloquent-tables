<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Services;

use BrickNPC\EloquentTables\Table;
use BrickNPC\EloquentTables\Attributes\Layout;

class LayoutFinder
{
    public function getLayout(Table $table): ?Layout
    {
        return $this->getLayoutByMethod($table) ?? $this->getLayoutByAttribute($table);
    }

    private function getLayoutByMethod(Table $table): ?Layout
    {
        if (method_exists($table, 'layout')) {
            $layout = $table->layout();

            return $layout instanceof Layout ? $layout : null;
        }

        return null;
    }

    private function getLayoutByAttribute(Table $table): ?Layout
    {
        $reflection = new \ReflectionClass($table);
        $attributes = $reflection->getAttributes(Layout::class);

        if (0 === count($attributes)) {
            return null;
        }

        /** @var Layout $layout */
        $layout = $attributes[0]->newInstance();

        return $layout;
    }
}
