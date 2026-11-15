<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Declined = 'declined';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('enums.booking_status.'.$this->value);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Declined, self::Completed, self::Cancelled], true);
    }
}
