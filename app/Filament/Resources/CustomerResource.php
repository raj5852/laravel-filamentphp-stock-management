<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'fas-user-plus';

    protected static ?string $navigationGroup = 'Peoples';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->placeholder('Enter Customer Name')
                    ->autocomplete(false)
                    ->rules([
                        'required',
                        'string',
                        'min:0',
                        'max:256',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->placeholder('Enter Customer Email')
                    ->rules([
                        'email',
                        'min:0',
                        'max:256',
                    ])
                    ->autocomplete(false)
                    ->email(),
                Forms\Components\Textarea::make('address')
                    ->placeholder('Write Customer Address')
                    ->rules([
                        'string',
                        'min:0',
                        'max:5000',
                    ]),
                Forms\Components\TextInput::make('phone')
                    ->placeholder('Enter Customer Phone')
                    ->autocomplete(false)
                    ->rules([
                        'string',
                        'min:0',
                        'max:256',
                    ])
                    ->tel()
                    ->required(),
                Forms\Components\TextInput::make('opening_receivable')
                    ->minValue(0)
                    ->hidden(fn (string $context) => $context === 'edit')
                    ->rules([
                        'numeric',
                        'min:0',
                        'max:9999999999',
                    ])
                    ->numeric(),
                Forms\Components\TextInput::make('opening_payable')
                    ->numeric()
                    ->hidden(fn (string $context) => $context === 'edit')
                    ->rules([
                        'numeric',
                        'min:0',
                        'max:9999999999',
                    ])
                    ->minValue(0),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Customer::query()
                ->withSum('orders', 'receivable')
                ->withSum('orders', 'paid')
                ->withSum('orders', 'due')
                ->latest())
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->label('Address')
                    ->searchable(),

                Tables\Columns\TextColumn::make('orders_sum_receivable')
                    ->default(0)
                    ->label('Receivable')
                    ->formatStateUsing(function ($state) {
                        return number_format($state ?: 0, 2).' TK';
                    }),
                Tables\Columns\TextColumn::make('orders_sum_paid')
                    ->default(0)
                    ->label('Paid')
                    ->formatStateUsing(function ($state) {
                        return number_format($state ?: 0, 2).' TK';
                    }),
                Tables\Columns\TextColumn::make('orders_sum_due')->label('Sale Due')
                    ->default(0)
                    ->formatStateUsing(function ($state) {
                        return number_format($state ?: 0, 2).' TK';
                    }),
                Tables\Columns\TextColumn::make('Wallet Balance')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Total Due')
                    ->searchable(),
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
            'index' => Pages\ListCustomers::route('/'),
            // 'create' => Pages\CreateCustomer::route('/create'),
            // 'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
