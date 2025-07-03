<?php

namespace App\Filament\Superadmin\Resources\UserInfoResource\Pages;

use App\Filament\Superadmin\Resources\UserInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserInfo extends EditRecord
{
    protected static string $resource = UserInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
