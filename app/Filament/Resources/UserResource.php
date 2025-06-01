<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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
                        ->placeholder('Email')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                        ->minLength(6)
                        ->dehydrated(fn ($state) => filled($state))
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->placeholder('Password')
                        ->maxLength(255),

                    // Add role selection field
                    Select::make('roles')
                        ->label('Role')
                        ->multiple()
                        ->relationship('roles', 'name')
                        ->preload()
                        ->searchable()
                        ->required(), // Added required validation

                    // Forms\Components\TextInput::make('type')
                    //     ->required(),
                    // Forms\Components\DatePicker::make('expires_at'),
                ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()->where([
                    'tenant_id' => auth()->user()->tenant_id,
                ])->where(
                    'id',
                    '!=',
                    auth()->user()->id
                )
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                // Add this new column to display roles
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable()
                    ->separator(', ')
                    ->label('Roles'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
