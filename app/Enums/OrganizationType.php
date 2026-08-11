<?php

namespace App\Enums;

enum OrganizationType: string
{
    case Single = 'single';
    case Dso = 'dso';

    public function subscriptionTierLabel(): string
    {
        return match ($this) {
            self::Single => __('Professional (Single Practice)'),
            self::Dso => __('Enterprise (Multi-location)'),
        };
    }
}
