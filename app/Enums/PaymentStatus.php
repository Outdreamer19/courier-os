<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case Paid = 'paid';
    case Waived = 'waived';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Unpaid',
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Waived => 'Waived',
            self::Refunded => 'Refunded',
        };
    }

    public function isOutstanding(): bool
    {
        return in_array($this, [self::Unpaid, self::Pending], true);
    }
}
