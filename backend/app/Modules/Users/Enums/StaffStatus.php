<?php

namespace App\Modules\Users\Enums;

enum StaffStatus: string
{
    case AVAILABLE = 'available';
    case ON_DUTY = 'on_duty';
    case ON_BREAK = 'on_break';
    case ON_LEAVE = 'on_leave';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::cases()
        );
    }
}


