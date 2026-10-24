<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case Scheduled = 'scheduled';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => __('Scheduled'),
            self::Confirmed => __('Confirmed'),
            self::CheckedIn => __('Checked In'),
            self::InProgress => __('In Treatment'),
            self::Completed => __('Completed'),
            self::Cancelled => __('Cancelled'),
            self::NoShow => __('No-Show'),
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Scheduled => 'bg-slate-100 text-slate-700 border-slate-200',
            self::Confirmed => 'bg-blue-50 text-blue-700 border-blue-200',
            self::CheckedIn => 'bg-amber-50 text-amber-800 border-amber-200',
            self::InProgress => 'bg-violet-50 text-violet-700 border-violet-200',
            self::Completed => 'bg-green-50 text-green-700 border-green-200',
            self::Cancelled => 'bg-red-50 text-red-700 border-red-200',
            self::NoShow => 'bg-orange-50 text-orange-800 border-orange-200',
        };
    }
}
