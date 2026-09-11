<?php

namespace App\Enums;

enum InventoryItemType: string
{
    case Material = 'material';
    case Equipment = 'equipment';
    case Consumable = 'consumable';
    case Medication = 'medication';

    public function label(): string
    {
        return match ($this) {
            self::Material => __('Dental Material'),
            self::Equipment => __('Equipment'),
            self::Consumable => __('Consumable'),
            self::Medication => __('Medication'),
        };
    }
}
