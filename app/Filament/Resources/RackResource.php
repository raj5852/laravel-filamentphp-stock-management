<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RackResource\Pages;
use App\Models\Rack;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RackResource extends Resource
{
    protected static ?string $model = Rack::class;

    protected static ?string $navigationIcon = 'fas-table';

    protected static ?string $navigationGroup = 'Product Information';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('rack_name')
                    ->placeholder('Rack Name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('rack_description')
                    ->placeholder('Rack Description')
                    ->maxLength(5000)
                    ->maxLength(255),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rack_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('rack_description')
                    ->searchable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\DeleteAction::make()->button()
                    ->before(function ($record, $action) {

                        if ($record->products()->exists()) {
                            Notification::make()
                                ->title("You can't delete it because it has products")
                                ->danger()
                                ->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
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
            'index' => Pages\ListRacks::route('/'),
            // 'create' => Pages\CreateRack::route('/create'),
            // 'edit' => Pages\EditRack::route('/{record}/edit'),
        ];
    }
}
