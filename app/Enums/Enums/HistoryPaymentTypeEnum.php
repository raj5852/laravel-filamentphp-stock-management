<?php

namespace App\Enums\Enums;

enum HistoryPaymentTypeEnum: int
{
    case CASH = 'cash';
    case TRANSFER = 'transfer';
}
