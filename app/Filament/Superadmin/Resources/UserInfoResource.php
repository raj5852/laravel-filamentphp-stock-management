<?php

namespace App\Filament\Superadmin\Resources;

use App\Filament\Superadmin\Resources\UserInfoResource\Pages;
use App\Models\User;
use App\Models\UserInfo;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserInfoResource extends Resource
{
    protected static ?string $model = UserInfo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->options(User::query()->whereDoesntHave('userInfo')->where('type', '1')->pluck('name', 'id'))
                    ->required()
                    ->default(request('user_id'))
                    ->searchable(),
                Forms\Components\TextInput::make('phone'),
                Forms\Components\Select::make('type')
                    ->options([
                        'whatsapp' => 'whatsapp',
                        'other' => 'other',
                    ])
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $phone = preg_replace('/[^\d]/', '', $get('phone'));

                        // Ensure it starts with '880'
                        if (substr($phone, 0, 3) !== '880') {
                            $phone = '880'.ltrim($phone, '0');
                        }

                        if ($state === 'whatsapp') {
                            $set('link', 'https://wa.me/'.$phone);
                        } else {
                            $set('link', '');
                        }
                    })
                    ->required(),
                Forms\Components\TextInput::make('link')
                    ->required(),
                Forms\Components\Textarea::make('description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name'),
                Tables\Columns\TextColumn::make('user.email'),
                Tables\Columns\TextColumn::make('description')->wrap()->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('link')
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListUserInfos::route('/'),
            'create' => Pages\CreateUserInfo::route('/create'),
            'edit' => Pages\EditUserInfo::route('/{record}/edit'),
        ];
    }
}
