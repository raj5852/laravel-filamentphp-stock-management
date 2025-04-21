<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
                        'max:5000',
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
                        'max:9999999999',
                    ])
                    ->numeric()
                    ->minValue(0),
                Forms\Components\TextInput::make('opening_payable')
                    ->hidden(fn (string $context) => $context === 'edit')
                    ->rules([
                        'numeric',
                        'min:0',
                        'max:9999999999',
                    ])
                    ->numeric()
                    ->minValue(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Supplier::query()->withSum('purchases', 'payable')->withSum('purchases', 'paid')->withSum('purchases', 'due'))
            ->columns([
                Tables\Columns\TextColumn::make('supplier_name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('phone'),
                Tables\Columns\TextColumn::make('address'),
                Tables\Columns\TextColumn::make('purchases_sum_payable')
                    ->label('Payable')
                    ->default(0)
                    ->formatStateUsing(function ($state) {
                        return number_format($state ?: 0, 2, '.', '').' TK';
                    }),
                Tables\Columns\TextColumn::make('purchases_sum_paid')
                    ->label('Paid')
                    ->default(0)
                    ->formatStateUsing(function ($state) {
                        return number_format($state ?: 0, 2, '.', '').' TK';
                    }),
                Tables\Columns\TextColumn::make('purchases_sum_due')
                    ->label('Due')
                    ->default(0)
                    ->formatStateUsing(function ($state) {
                        return number_format($state ?: 0, 2, '.', '').' TK';
                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Action::make('report')
                        ->label('Report')
                        ->icon('fas-flag')
                        ->url(fn (Supplier $record): string => route('filament.admin.resources.suppliers.report', $record)),
                    Action::make('list')
                        ->label('Purchase List')
                        ->icon('fas-list')
                        ->url(fn (Supplier $record): string => route('filament.admin.resources.purchases.index', ['tableFilters[supplier_id][value]' => $record->id])),

                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record, $action) {

                            $purchase = $record->purchases()->count();

                            if ($purchase > 0) {
                                Notification::make()
                                    ->title("You can't delete it.")
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
            'report' => Pages\Report::route('/report/{record}'),
            // 'create' => Pages\CreateSupplier::route('/create'),
            // 'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
