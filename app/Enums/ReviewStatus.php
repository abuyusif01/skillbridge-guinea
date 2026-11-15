<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case Published = 'published';
    case Reported = 'reported';
    case Hidden = 'hidden';

    public function label(): string
    {
        return __('enums.review_status.'.$this->value);
    }
}
