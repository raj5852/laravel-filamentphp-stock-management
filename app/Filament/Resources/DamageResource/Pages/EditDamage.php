<?php

namespace App\Filament\Resources\DamageResource\Pages;

use App\Filament\Resources\DamageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDamage extends EditRecord
{
    protected static string $resource = DamageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
