<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Online = 'online';
    case InPerson = 'in_person';
    case Manual = 'manual';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::InPerson => 'In person',
            self::Manual => 'Manual',
            self::Unknown => 'Unknown',
        };
    }
}
