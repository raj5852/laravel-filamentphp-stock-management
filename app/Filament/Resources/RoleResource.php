<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-finger-print';

    protected static ?string $navigationGroup = 'Setting & Customize';

    public static function form(Form $form): Form
    {
        $permissionCount = Permission::count();

        return $form
            ->schema([
                Card::make([
                    Card::make([
                        TextInput::make('name')->placeholder('Role name')->required(),
                        Toggle::make('select_all')
                            ->inline(false)
                            ->label('Select All Permissions')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $permissionIds = Permission::pluck('id')->toArray();
                                    $set('permissions', $permissionIds);
                                } else {
                                    $set('permissions', []);
                                }
                            })
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Toggle $component, $state, $record, callable $set) use ($permissionCount) {
                                // Check if this is an existing record with permissions
                                if ($record) {
                                    // If all permissions are selected, set the toggle to true
                                    $selectedPermissionsCount = $record->permissions->count();
                                    $set('select_all', $selectedPermissionsCount === $permissionCount);
                                }
                            }),
                    ])->columns(2),

                    CheckboxList::make('permissions')
                        ->relationship('permissions', 'name', function ($query) {
                            return $query->orderBy('id', 'asc');
                        })
                        ->columns(3)
                        ->helperText('Select permissions for this role')
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set) use ($permissionCount) {
                            if (is_array($state) && count($state) === $permissionCount) {
                                $set('select_all', true);
                            } else {
                                $set('select_all', false);
                            }
                        }),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->getStateUsing(function (Role $record) {
                        return DB::table('model_has_roles')
                            ->where('role_id', $record->id)
                            ->where('model_type', 'App\\Models\\User')
                            ->count();
                    })
                    ->alignCenter()
                    ->sortable(false),
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
