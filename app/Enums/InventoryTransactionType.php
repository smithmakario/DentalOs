<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case Restock = 'restock';
    case Consume = 'consume';
    case Adjustment = 'adjustment';
    case Expired = 'expired';
    case Damaged = 'damaged';

    public function label(): string
    {
        return match ($this) {
            self::Restock => __('Restock'),
            self::Consume => __('Consume'),
            self::Adjustment => __('Adjustment'),
            self::Expired => __('Expired'),
            self::Damaged => __('Damaged'),
        };
    }
}
