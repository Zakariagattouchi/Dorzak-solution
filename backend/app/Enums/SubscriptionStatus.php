<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case ACTIVE = 'ACTIVE';
    case PAST_DUE = 'PAST_DUE';
    case CANCELLED = 'CANCELLED';
}
