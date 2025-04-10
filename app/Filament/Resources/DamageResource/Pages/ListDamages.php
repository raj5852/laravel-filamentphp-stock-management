<?php

namespace App\Filament\Resources\DamageResource\Pages;

use App\Filament\Resources\DamageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDamages extends ListRecords
{
    protected static string $resource = DamageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
