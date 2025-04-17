<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OwnerResource\Pages;
use App\Models\Owner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class OwnerResource extends Resource
{
    protected static ?string $model = Owner::class;

    protected static ?string $navigationIcon = 'fas-users';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->placeholder('Owner Name')
                    ->autocomplete(false)
                    ->rules([
                        'string',
                        'min:0',
                        'max:256',
                        'required',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('mobile')
                    ->autocomplete(false)
                    ->rules([
                        'max:256',
                        'min:0',
                        'string',
                    ])
                    ->placeholder('Mobile Number'),
                Forms\Components\Textarea::make('address')
                    ->placeholder('Address')
                    ->autocomplete(false)
                    ->rules([
                        'max:5000',
                        'min:0',
                        'string',

                    ])
                    ->columnSpanFull(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mobile')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('invested')
                    ->getStateUsing(function ($record) {
                        return number_format($record->invested, 2);
                    }),
                Tables\Columns\TextColumn::make('withdrawn')
                    ->getStateUsing(function ($record) {
                        return number_format($record->withdrawn, 2);
                    }),
                Tables\Columns\TextColumn::make('balance')
                    ->getStateUsing(function ($record) {
                        return number_format($record->balance, 2);
                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record, $action) {
                            if ($record->histories()->count() > 0) {
                                Notification::make()
                                    ->title("You can't delete it because it has transaction")
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ])
                    ->dropdown(true)
                    ->label('Actions')
                    ->button()
                    ->size('sm')
                    ->icon('fas-gears'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->paginated(false);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOwners::route('/'),
            // 'create' => Pages\CreateOwner::route('/create'),
            // 'edit' => Pages\EditOwner::route('/{record}/edit'),
        ];
    }
}
