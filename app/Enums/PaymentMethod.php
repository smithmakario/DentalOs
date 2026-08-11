<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Pos = 'pos';
    case Transfer = 'transfer';
    case Credit = 'credit';
    case Hmo = 'hmo';

    public function label(): string
    {
        return match ($this) {
            self::Cash => __('Cash'),
            self::Pos => __('POS'),
            self::Transfer => __('Transfer'),
            self::Credit => __('Credit'),
            self::Hmo => __('HMO'),
        };
    }
}
