<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Worker = 'worker';
    case Admin = 'admin';

    public function label(): string
    {
        return __('enums.user_role.'.$this->value);
    }
}
