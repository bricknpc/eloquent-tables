<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

enum CellStyle
{
    case AlignLeft;
    case AlignCenter;
    case AlignRight;
    case AlignJustify;
    case AlignBetween;
    case AlignTop;
    case AlignMiddle;
    case AlignBottom;

    public function toCssClass(Theme $theme, bool $flex = false): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match ($this) {
                self::AlignLeft     => $flex ? 'justify-content-start' : 'text-start',
                self::AlignCenter   => $flex ? 'justify-content-center' : 'text-center',
                self::AlignRight    => $flex ? 'justify-content-end' : 'text-end',
                self::AlignJustify  => $flex ? 'justify-content-stretch' : 'text-justify',
                self::AlignBetween  => $flex ? 'justify-content-between' : '',
                self::AlignTop      => $flex ? 'align-items-start' : 'align-text-top',
                self::AlignMiddle   => $flex ? 'align-items-center' : 'align-middle',
                self::AlignBottom   => $flex ? 'align-items-end' : 'align-text-bottom',
            },
        };
    }
}
