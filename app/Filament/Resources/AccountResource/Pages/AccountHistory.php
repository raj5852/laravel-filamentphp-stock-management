<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Resources\AccountResource;
use App\HistoryTypeEnum;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class AccountHistory extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = AccountResource::class;

    protected static ?string $title = 'Transaction History';

    protected static string $view = 'filament.resources.account-resource.pages.account-history';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table

            ->query(fn () => $this->record->histories()->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->date(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '')),
                TextColumn::make('type')
                    ->getStateUsing(fn ($record) => $record->type instanceof HistoryTypeEnum
                            ? $record->type->getLabelText()
                            : HistoryTypeEnum::from((int) $record->type)->getLabelText()
                    ),
                TextColumn::make('note'),

            ])
            ->heading(new HtmlString('Account Name: '.$this->record->name))
            ->paginated([10, 25, 50, 100]);

    }
}
