<?php

namespace App\Enums;

enum RegistrationRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('Pending'),
            self::Approved => __('Approved'),
            self::Rejected => __('Rejected'),
            self::Completed => __('Completed'),
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Approved => 'bg-blue-100 text-blue-800',
            self::Rejected => 'bg-red-100 text-red-800',
            self::Completed => 'bg-emerald-100 text-emerald-800',
        };
    }
}
