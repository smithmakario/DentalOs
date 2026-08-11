<?php

namespace App\Support;

class Money
{
    public static function naira(float|int|string|null $amount): string
    {
        if ($amount === null || $amount === '') {
            return '₦0';
        }

        return '₦'.number_format((float) $amount, 2);
    }
}
