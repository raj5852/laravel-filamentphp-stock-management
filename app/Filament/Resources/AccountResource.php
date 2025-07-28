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
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'fas-building-columns';

    protected static ?int $navigationSort = 1;

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
                    ->rules([
                        'numeric',
                        'min:0',
                        'max:9999999999',
                    ])
                    ->autocomplete(false)
                    ->hidden(fn (string $context) => $context === 'edit')
                    ->numeric(),

            ]);
    }

    public static function table(Table $table): Table
    {
        $userId = auth()->user()->tenant_id;
        $ownerOptions = Owner::query()->pluck('name', 'id');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                // Tables\Columns\TextColumn::make('opening_balance')
                //     ->getStateUsing(function ($record) {
                //         return number_format($record->opening_balance, 2);
                //     })
                //     ->numeric(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        return number_format($record->current_balance, 2);
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Status')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('add_balance')
                        ->label('Add Balance')
                        ->icon('fas-plus')
                        // ->button()
                        ->size('sm')
                        ->outlined()
                        // ->color('success')
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
                                    'max:9999999999',
                                ])
                                ->prefix('৳'),
                            Forms\Components\Textarea::make('note')
                                ->rules([
                                    'string',
                                    'max:5000',
                                ])
                                ->label('Note'),
                            Forms\Components\Select::make('owner')
                                ->required()
                                ->rules([
                                    'required',
                                    Rule::exists('owners', 'id')->where('tenant_id', $userId),
                                ])
                                ->options($ownerOptions),

                        ])
                        ->modalCancelAction(false)
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
                        ->modalSubmitActionLabel('Add Balance')
                        ->modalWidth('md'),

                    Action::make('withdraw_balance')
                        ->label('Withdraw Balance')
                        ->icon('fas-outdent')
                        // ->button()
                        ->size('sm')
                        ->outlined()
                        // ->color('danger')
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
                                    'max:9999999999',
                                ])
                                ->prefix('৳'),
                            Forms\Components\Textarea::make('note')
                                ->rules([
                                    'string',
                                    'min:0',
                                    'max:5000',
                                ])
                                ->label('Note'),
                            Forms\Components\Select::make('owner')
                                ->required()
                                ->rules([
                                    'required',
                                    Rule::exists('owners', 'id')->where('tenant_id', $userId),
                                ])
                                ->options($ownerOptions),

                        ])
                        ->modalCancelAction(false)
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
                        ->modalSubmitActionLabel('Withdraw Balance')
                        ->modalWidth('md'),

                    Action::make('transfer_balance')
                        ->label('Transfer Balance')
                        ->icon('fas-money-bill-transfer')
                        // ->button()
                        ->size('sm')
                        ->outlined()
                        // ->color('warning')
                        ->modalHeading('Transfer Balance')
                        ->form(
                            function (Account $account) use ($userId) {
                                return [

                                    Forms\Components\Select::make('to_account')
                                        ->required()
                                        ->rules([
                                            'required',
                                            Rule::exists('accounts', 'id')->where('tenant_id', $userId)
                                                ->whereNot('id', $account->id),
                                        ])
                                        ->options(Account::query()->where('tenant_id', $userId)->whereNot('id', $account->id)->pluck('name', 'id')),

                                    Forms\Components\TextInput::make('amount')
                                        ->label('Amount')
                                        ->numeric()
                                        ->required()
                                        ->minValue(0)
                                        ->rules([
                                            'required',
                                            'numeric',
                                            'min:0',
                                            'max:9999999999',
                                        ])
                                        ->prefix('৳'),

                                    Forms\Components\Textarea::make('note')
                                        ->rules([
                                            'string',
                                            'max:5000',

                                        ])
                                        ->label('Note'),

                                ];
                            }
                        )

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
                        ->modalCancelAction(false)
                        ->modalSubmitActionLabel('Transfer')
                        ->modalWidth('md'),
                    Action::make('toggle_status')
                        ->icon(fn (Account $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->label(fn (Account $record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->color(fn (Account $record) => $record->is_active ? 'danger' : 'success')
                        ->size('sm')
                        ->outlined()
                        ->requiresConfirmation()
                        ->modalHeading(fn (Account $record) => $record->is_active ? 'Deactivate Account' : 'Activate Account')
                        ->modalDescription(fn (Account $record) => $record->is_active
                            ? 'Are you sure you want to deactivate this account?'
                            : 'Are you sure you want to activate this account?'
                        )
                        ->modalSubmitActionLabel(fn (Account $record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->action(function (Account $record) {
                            $record->update(['is_active' => ! $record->is_active]);

                            Notification::make()
                                ->title($record->is_active ? 'Account Activated' : 'Account Deactivated')
                                ->success()
                                ->send();
                        }),
                    Action::make('History')
                        ->icon('fas-history')
                        // ->button()
                        ->size('sm')
                        // ->color('success')
                        ->url(fn (Account $record) => route('filament.admin.resources.accounts.history', ['record' => $record->id])),
                ])->dropdown(true)
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
