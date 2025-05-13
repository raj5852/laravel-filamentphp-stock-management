<?php

namespace App;

enum UserTypeEnum: int
{
    case USERROLE = 0;
    case USER = 1;
    case SUPERADMIN = 2;

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
