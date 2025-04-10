<?php

namespace App;

enum HistoryTypeEnum: int
{
    case OPENING_BALANCE = 1;
    case RECEIVED = 2;
    case SPENT_OR_WITHDRAW = 3;

    public function getLabelText(): string
    {
        return match ($this) {
            self::OPENING_BALANCE => 'Opening Balance',
            self::RECEIVED => 'Received',
            self::SPENT_OR_WITHDRAW => 'Spent / Withdraw',
        };
    }
}
