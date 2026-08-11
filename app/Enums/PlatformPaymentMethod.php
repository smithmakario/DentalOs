<?php

namespace App\Enums;

enum PlatformPaymentMethod: string
{
    case Paystack = 'paystack';
    case Manual = 'manual';
}
