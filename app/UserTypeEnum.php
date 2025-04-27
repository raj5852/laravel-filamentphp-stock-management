<?php

namespace App;

enum UserTypeEnum: int
{
    case USERROLE = 0;
    case USER = 1;
    case SUPERADMIN = 2;
}
