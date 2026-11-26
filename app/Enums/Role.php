<?php

namespace App\Enums;

enum Role: string
{
    case Staff = 'staff';   // Personnel: own registries + upload completion files
    case Oic = 'oic';       // OIC / Unit Head: all entries, summary grid, status changes
    case Admin = 'admin';   // Full access

    public function label(): string
    {
        return match ($this) {
            self::Staff => 'Staff',
            self::Oic => 'OIC / Unit Head',
            self::Admin => 'Admin',
        };
    }
}
