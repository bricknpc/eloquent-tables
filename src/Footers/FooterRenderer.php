<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Footers;

use BrickNPC\EloquentTables\Table;
use Illuminate\Support\Collection;
use BrickNPC\EloquentTables\Column;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Query\Builder;
use BrickNPC\EloquentTables\ValueObjects\ResolvedFooter;

/**
 * @template TModel of Model
 */
readonly class FooterRenderer
{
    /**
     * @param FooterResolver<TModel> $resolver
     */
    public function __construct(
        private FooterResolver $resolver,
    ) {}

    /**
     * @param Table<TModel>          $table
     * @param array<Column<TModel>>  $columns
     * @param Collection<int, Model> $rows
     */
    public function build(
        Table $table,
        array $columns,
        Collection $rows,
        ?Builder $query,
        int $leadingCells = 0,
    ): ResolvedFooter {
        $footerRows = $table->footer();

        if ($footerRows === []) {
            return new ResolvedFooter([], 0);
        }

        return $this->resolver->resolve($footerRows, $columns, $rows, $query, $leadingCells);
    }
}
