<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Actions\ValueObjects;

use Illuminate\Contracts\View\View;
use Illuminate\Contracts\Support\Htmlable;

final class RenderBuffer
{
    /** @var list<null|Htmlable|string|\Stringable|View> */
    private array $chunks = [];

    public function add(Htmlable|string|\Stringable|View|null $html): void
    {
        $this->chunks[] = $html;
    }

    public function render(): string
    {
        return implode('', array_map(function (Htmlable|string|\Stringable|View|null $chunk) {
            return match (true) {
                $chunk instanceof Htmlable => $chunk->toHtml(),
                $chunk instanceof View     => $chunk->render(),
                default                    => (string) $chunk,
            };
        }, $this->chunks));
    }
}
