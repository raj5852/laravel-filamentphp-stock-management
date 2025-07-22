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
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Setting & Customize';

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
                        ->unique(ignoreRecord: true)
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
                        ->relationship('roles', 'name', fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))
                        ->preload()
                        ->searchable()
                        // ->rules(['required', 'array', 'min:1', Rule::exists('roles', 'id')->where('tenant_id', auth()->user()->tenant_id)])
                        ->required(),
                    // ->rules(['required', 'array', 'min:1', Rule::exists('roles', 'id')->where(fn ($query) => $query->where('tenant_id', auth()->user()->tenant_id))])
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
                    'type',
                    '!=',
                    '1'
                )
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),

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
                DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            // 'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
