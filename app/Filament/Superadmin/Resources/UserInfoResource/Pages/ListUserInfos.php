<?php

namespace App\Filament\Superadmin\Resources\UserInfoResource\Pages;

use App\Filament\Superadmin\Resources\UserInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserInfos extends ListRecords
{
    protected static string $resource = UserInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
