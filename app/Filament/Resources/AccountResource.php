<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\HistoryTypeEnum;
use App\Models\Account;
use App\Models\Owner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'fas-building-columns';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->placeholder('Account Name')
                    ->autocomplete(false)
                    ->rules([
                        'max:256',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('opening_balance')
                    ->placeholder('Opening Balance')
                    ->required()
                    ->default(0)
                    ->maxLength(10)
                    ->rules([
                        'required',
                        'min:0',
                        'max_digits:10',
                        'numeric',
                    ])
                    ->numeric(),

            ]);
    }

    public static function table(Table $table): Table
    {
        $userId = auth()->user()->id;
        $ownerOptions = Owner::query()->pluck('name', 'id');
        // $accounts = ;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('opening_balance')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('add_balance')
                    ->label('Add Balance')
                    ->icon('fas-plus')
                    ->button()
                    ->size('sm')
                    ->outlined()
                    ->color('success')
                    ->modalHeading('Add Balance')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->rules([
                                'required',
                                'numeric',
                                'min:0',
                                'max_digits:10',
                            ])
                            ->prefix('৳'),
                        Forms\Components\Textarea::make('note')
                            ->rules([
                                'string',
                                'max:65535',
                            ])
                            ->label('Note'),
                        Forms\Components\Select::make('owner')
                            ->required()
                            ->rules([
                                Rule::exists('owners', 'id')->where('tenant_id', $userId),
                            ])
                            ->options($ownerOptions),

                    ])
                    ->modalCancelActionLabel('Close')
                    ->action(function (Account $record, array $data) {

                        DB::transaction(function () use ($record, $data) {

                            $record->increment('current_balance', $data['amount']);
                            $record->histories()->create([
                                'date' => now(),
                                'owner_id' => $data['owner'],
                                'amount' => $data['amount'],
                                'type' => HistoryTypeEnum::RECEIVED->value,
                                'note' => $data['note'],
                            ]);

                            $owner = Owner::find($data['owner']);
                            $owner->increment('invested', $data['amount']);
                            $owner->increment('balance', $data['amount']);

                        });

                        Notification::make()
                            ->title('Balance Added Successfully')
                            ->success()
                            ->send();

                    })
                    ->modalWidth('sm'),

                Action::make('withdraw_balance')
                    ->label('Withdraw Balance')
                    ->icon('fas-outdent')
                    ->button()
                    ->size('sm')
                    ->outlined()
                    ->color('danger')
                    ->modalHeading('Withdraw Balance')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->rules([
                                'required',
                                'numeric',
                                'min:0',
                                'max_digits:10',
                            ])
                            ->prefix('৳'),
                        Forms\Components\Textarea::make('note')
                            ->rules([
                                'string',
                                'min:0',
                                'max:65535',
                            ])
                            ->label('Note'),
                        Forms\Components\Select::make('owner')
                            ->required()
                            ->rules([
                                Rule::exists('owners', 'id')->where('tenant_id', $userId),
                            ])
                            ->options($ownerOptions),

                    ])
                    ->modalCancelActionLabel('Close')
                    ->action(function (Account $record, array $data) {

                        DB::transaction(function () use ($record, $data) {

                            $record->decrement('current_balance', $data['amount']);
                            $record->histories()->create([
                                'date' => now(),
                                'owner_id' => $data['owner'],
                                'amount' => $data['amount'],
                                'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                                'note' => $data['note'],
                            ]);

                            $owner = Owner::find($data['owner']);
                            $owner->increment('withdrawn', $data['amount']);
                            $owner->decrement('balance', $data['amount']);

                        });

                        Notification::make()
                            ->title('Balance Withdraw Successfully')
                            ->success()
                            ->send();

                    })
                    ->modalWidth('sm'),

                Action::make('transfer_balance')
                    ->label('Transfer Balance')
                    ->icon('fas-money-bill-transfer')
                    ->button()
                    ->size('sm')
                    ->outlined()
                    ->color('warning')
                    ->modalHeading('Transfer Balance')
                    ->form([

                        Forms\Components\Select::make('to_account')
                            ->required()
                            ->rules([
                                Rule::exists('accounts', 'id')->where('tenant_id', $userId),
                            ])
                            ->options(function (Account $account) {
                                return Account::where('id', '!=', $account->id)->pluck('name', 'id');
                            }),

                        Forms\Components\TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->rules([
                                'required',
                                'numeric',
                                'min:0',
                                'max_digits:10',
                            ])
                            ->prefix('৳'),

                        Forms\Components\Textarea::make('note')
                            ->rules([
                                'string',
                                'max:65535',

                            ])
                            ->label('Note'),

                    ])
                    ->modalCancelActionLabel('Close')
                    ->action(function (Account $record, array $data) {

                        DB::transaction(function () use ($record, $data) {

                            $record->decrement('current_balance', $data['amount']);
                            $record->histories()->create([
                                'date' => now(),
                                // 'owner_id' => $data['owner'],
                                'amount' => $data['amount'],
                                'type' => HistoryTypeEnum::SPENT_OR_WITHDRAW->value,
                                'note' => $data['note'],
                            ]);

                            $toAccount = Account::find($data['to_account']);
                            $toAccount->increment('current_balance', $data['amount']);
                            $toAccount->histories()->create([
                                'date' => now(),
                                // 'owner_id' => $data['owner'],
                                'amount' => $data['amount'],
                                'type' => HistoryTypeEnum::RECEIVED->value,
                                'note' => $data['note'],
                            ]);

                        });

                        Notification::make()
                            ->title('Balance Transfer Successfully')
                            ->success()
                            ->send();

                    })
                    ->modalWidth('sm'),

                Action::make('History')
                    ->icon('fas-history')
                    ->button()
                    ->size('sm')
                    ->color('success')
                    ->url(fn (Account $record) => route('filament.admin.resources.accounts.history', ['record' => $record->id])),

            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            // ->paginated([10, 25, 50, 100])
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
            'index' => Pages\ListAccounts::route('/'),
            'history' => Pages\AccountHistory::route('/history/{record}'),
            // 'create' => Pages\CreateAccount::route('/create'),
            // 'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
