<?php

namespace App\Enums;

enum SubscriptionPaymentStatus: string
{
    case Pending = 'pending';
    case AwaitingVerification = 'awaiting_verification';
    case Completed = 'completed';
    case Failed = 'failed';
}
