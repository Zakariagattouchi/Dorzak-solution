<?php

namespace App\Enums;

enum StockMovementType: string
{
    case INITIAL = 'INITIAL';
    case SALE = 'SALE';
    case CANCEL_RETURN = 'CANCEL_RETURN';
    case ADJUSTMENT = 'ADJUSTMENT';
    case RESTOCK = 'RESTOCK';
}
