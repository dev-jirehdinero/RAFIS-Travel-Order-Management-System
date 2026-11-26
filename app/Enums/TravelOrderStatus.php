<?php

namespace App\Enums;

enum TravelOrderStatus: string
{
    case NotStarted = 'Not started';
    case Submitted = 'SUBMITTED';
    case Approved = 'APPROVED';

    /** Statuses that count as a completed accomplishment on the progress bars. */
    public static function completed(): array
    {
        return [self::Submitted, self::Approved];
    }

    /** @return string[] */
    public static function completedValues(): array
    {
        return array_map(fn (self $s) => $s->value, self::completed());
    }

    /** @return string[] */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Tailwind classes for the pill tag. Kept as literal strings so Tailwind can detect them. */
    public function pillClasses(): string
    {
        return match ($this) {
            self::NotStarted => 'border-gray-300 bg-gray-50 text-gray-700',
            self::Submitted => 'border-blue-300 bg-blue-50 text-blue-700',
            self::Approved => 'border-green-300 bg-green-50 text-green-700',
        };
    }
}
