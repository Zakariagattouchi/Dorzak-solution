<?php

namespace App\Enums;

enum OrderSource: string
{
    case POS = 'POS';
    case ONLINE = 'ONLINE';
}
