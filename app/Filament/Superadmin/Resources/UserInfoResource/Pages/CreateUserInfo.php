<?php

namespace App\Filament\Superadmin\Resources\UserInfoResource\Pages;

use App\Filament\Superadmin\Resources\UserInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUserInfo extends CreateRecord
{
    protected static string $resource = UserInfoResource::class;
}
