<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'fas-users-gear';

    protected static ?string $navigationGroup = 'Peoples';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('supplier_name')
                    ->placeholder('Enter Supplier Name')
                    ->autocomplete(false)
                    ->rules([
                        'required',
                        'string',
                        'min:0',
                        'max:256',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->placeholder('Enter Supplier Email')
                    ->rules([
                        'email',
                        'min:0',
                        'max:256',
                    ])
                    ->autocomplete(false)

                    ->email(),
                Forms\Components\Textarea::make('address')
                    ->placeholder('Write Supplier Address')
                    ->rules([
                        'string',
                        'min:0',
                        'max:65535',
                    ]),
                Forms\Components\TextInput::make('phone')
                    ->placeholder('Enter Supplier Phone')
                    ->autocomplete(false)
                    ->tel()
                    ->rules([
                        'required',
                        'string',
                        'min:0',
                        'max:256',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('opening_receivable')
                    ->hidden(fn (string $context) => $context === 'edit')
                    ->rules([
                        'numeric',
                        'min:0',
                        'max_digits:12',
                    ])
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('opening_payable')
                    ->hidden(fn (string $context) => $context === 'edit')
                    ->rules([
                        'numeric',
                        'min:0',
                        'max_digits:12',
                    ])
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('opening_receivable')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('opening_payable')
                    ->numeric()
                    ->sortable(),

            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
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
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListSuppliers::route('/'),
            // 'create' => Pages\CreateSupplier::route('/create'),
            // 'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
