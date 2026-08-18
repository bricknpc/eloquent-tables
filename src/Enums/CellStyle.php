<?php

declare(strict_types=1);

namespace BrickNPC\EloquentTables\Enums;

use BrickNPC\EloquentTables\Contracts\Style;

enum CellStyle implements Style
{
    case AlignLeft;
    case AlignCenter;
    case AlignRight;
    case AlignJustify;
    case AlignBetween;
    case AlignTop;
    case AlignMiddle;
    case AlignBottom;

    case BackgroundPrimary;
    case BackgroundSecondary;
    case BackgroundTertiary;
    case BackgroundQuaternary;
    case BackgroundSuccess;
    case BackgroundWarning;
    case BackgroundDanger;
    case BackgroundInfo;
    case BackgroundLight;
    case BackgroundDark;

    case TextPrimary;
    case TextSecondary;
    case TextTertiary;
    case TextQuaternary;
    case TextSuccess;
    case TextWarning;
    case TextDanger;
    case TextInfo;
    case TextLight;
    case TextDark;

    case FontLight;
    case FontNormal;
    case FontSemibold;
    case FontBold;

    public function toCssClass(Theme $theme): string
    {
        return match ($theme) {
            Theme::Bootstrap5 => match ($this->family()) {
                StyleFamily::Alignment  => $this->toAlignmentCssClass(),
                StyleFamily::Background => sprintf('table-%s', $this->colour()),
                StyleFamily::TextColour => sprintf('text-%s', $this->colour()),
                StyleFamily::FontWeight => sprintf('fw-%s', $this->weight()),
            },
        };
    }

    public function target(): StyleTarget
    {
        return match ($this->family()) {
            StyleFamily::Alignment => StyleTarget::Content,
            default                => StyleTarget::Cell,
        };
    }

    public function family(): StyleFamily
    {
        return match ($this) {
            self::AlignLeft,
            self::AlignCenter,
            self::AlignRight,
            self::AlignJustify,
            self::AlignBetween,
            self::AlignTop,
            self::AlignMiddle,
            self::AlignBottom,
                => StyleFamily::Alignment,
            self::BackgroundPrimary,
            self::BackgroundSecondary,
            self::BackgroundTertiary,
            self::BackgroundQuaternary,
            self::BackgroundSuccess,
            self::BackgroundWarning,
            self::BackgroundDanger,
            self::BackgroundInfo,
            self::BackgroundLight,
            self::BackgroundDark,
                => StyleFamily::Background,
            self::TextPrimary,
            self::TextSecondary,
            self::TextTertiary,
            self::TextQuaternary,
            self::TextSuccess,
            self::TextWarning,
            self::TextDanger,
            self::TextInfo,
            self::TextLight,
            self::TextDark,
                => StyleFamily::TextColour,
            self::FontLight, self::FontNormal, self::FontSemibold, self::FontBold => StyleFamily::FontWeight,
        };
    }

    private function toAlignmentCssClass(): string
    {
        return match ($this) {
            self::AlignLeft => 'justify-content-start',
            self::AlignCenter => 'justify-content-center',
            self::AlignRight => 'justify-content-end',
            self::AlignJustify => 'justify-content-stretch',
            self::AlignBetween => 'justify-content-between',
            self::AlignTop => 'align-items-start',
            self::AlignMiddle => 'align-items-center',
            self::AlignBottom => 'align-items-end',
            default => '',
        };
    }

    private function colour(): string
    {
        return match ($this) {
            self::BackgroundPrimary, self::TextPrimary => 'primary',
            self::BackgroundSecondary, self::TextSecondary => 'secondary',
            self::BackgroundTertiary, self::TextTertiary => 'tertiary',
            self::BackgroundQuaternary, self::TextQuaternary => 'quaternary',
            self::BackgroundSuccess, self::TextSuccess => 'success',
            self::BackgroundWarning, self::TextWarning => 'warning',
            self::BackgroundDanger, self::TextDanger => 'danger',
            self::BackgroundInfo, self::TextInfo => 'info',
            self::BackgroundLight, self::TextLight => 'light',
            self::BackgroundDark, self::TextDark => 'dark',
            default => '',
        };
    }

    private function weight(): string
    {
        return match ($this) {
            self::FontLight => 'light',
            self::FontNormal => 'normal',
            self::FontSemibold => 'semibold',
            self::FontBold => 'bold',
            default => '',
        };
    }
}
