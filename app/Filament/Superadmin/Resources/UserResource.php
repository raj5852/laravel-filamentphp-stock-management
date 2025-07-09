<?php

namespace App\Filament\Superadmin\Resources;

use App\Filament\Superadmin\Resources\UserResource\Pages;
use App\Models\User;
use App\UserTypeEnum;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->placeholder('Name')
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->placeholder('Email')
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Forms\Components\DateTimePicker::make('email_verified_at')
                        ->default(now())
                        ->hidden(),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->placeholder('Password')
                        ->hidden(fn (string $context) => $context === 'edit')
                        ->maxLength(255),

                    Select::make('type')
                        ->required()
                        ->options([
                            UserTypeEnum::USER->value => 'User',
                        ])
                        ->default(UserTypeEnum::USER->value),
                    TextInput::make('expires_at')
                        ->numeric()
                        ->label('Add Month')
                        ->hidden(fn (string $context) => $context === 'edit')
                        ->minValue(1),

                    TextInput::make('sms_count')
                        ->default(0)
                        ->required()
                        ->numeric()
                        ->minValue(0)
                        ->label('SMS Count'),

                    DatePicker::make('expires_at')
                        ->hidden(fn (string $context) => $context === 'create')
                        ->required()
                        ->native(false),

                ])->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        $userCount = User::where('type', UserTypeEnum::USER)->count();

        return $table
            ->query(User::query()->with('userInfo')->latest()->where('type', UserTypeEnum::USER))
            ->heading('All Users ('.$userCount.')')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->label('Status')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    // ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('password')
                    ->copyable()
                    ->label('Login URL')
                    ->getStateUsing(function ($record) {
                        return '<b class="cursor-pointer border p-3 bg-gray-100">Click to Copy</b>';
                    })
                    ->html()
                    ->copyableState(function ($record) {
                        return config('app.url').'/redirect-to-user/'.$record->email.'?password='.$record->password;
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('Available days')
                    ->getStateUsing(function ($record) {
                        $todayStartDate = today();
                        $endDate = Carbon::parse($record->expires_at);

                        return $todayStartDate->diffInDays($endDate);
                    }),

                Tables\Columns\TextColumn::make('sms_count')
                    ->label('SMS Count')
                    ->getStateUsing(function ($record) {
                        return $record->sms_count;
                    }),

                Tables\Columns\TextColumn::make('userInfo.description')
                    ->label('Description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Status')
                    ->placeholder('All')
                    ->query(function ($query, array $data) {
                        return $query->when($data['value'] !== null, function ($query) use ($data) {
                            return $query->where('status', $data['value']);
                        });
                    }),

                Tables\Filters\Filter::make('expired')
                    ->form([
                        Forms\Components\Select::make('expired_status')
                            ->options([
                                'yes' => 'Expired',
                                'no' => 'Not Expired',
                            ])
                            ->placeholder('All'),
                    ])
                    ->label('Expiration Status')
                    ->query(function ($query, array $data) {
                        return $query->when($data['expired_status'] === 'yes', function ($query) {
                            return $query->where('expires_at', '<', now());
                        })->when($data['expired_status'] === 'no', function ($query) {
                            return $query->where('expires_at', '>=', now());
                        });
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Action::make('link')
                    ->url(function ($record) {
                        $link = $record?->userInfo?->link;
                        if ($link == '') {
                            return '/superadmin/user-infos/create?user_id='.$record->id;
                        } else {
                            return $link;
                        }
                    })
                    ->label(function ($record) {
                        $link = $record?->userInfo?->link;
                        if ($link == '') {
                            return 'Add Details';
                        } else {
                            return 'Link';
                        }
                    })
                    ->openUrlInNewTab() // This adds target="_blank"
                    ->icon('heroicon-s-link')
                    ->color(function ($record) {
                        $link = $record?->userInfo?->link;

                        if ($link == '') {
                            return Color::Red;
                        } else {
                            return Color::Blue;
                        }
                    })
                    ->button(),
                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('toggle_status')
                        ->label(fn (User $record): string => $record->status ? 'Deactivate' : 'Activate')
                        ->icon(fn (User $record): string => $record->status ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn (User $record): string => $record->status ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(function (User $record): void {
                            $record->status = ! $record->status;
                            $record->save();
                        }),
                    Action::make('Setting')
                        ->label('Setting')
                        ->icon('heroicon-s-printer')
                        ->url(fn (User $record) => route('filament.superadmin.resources.users.setting', ['record' => $record->id])),

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
            'index' => Pages\ListUsers::route('/'),
            'setting' => Pages\Setting::route('/setting/{record}'),

            // 'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
