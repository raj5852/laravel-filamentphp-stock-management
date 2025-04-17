<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationGroup = 'Product Information';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('unit_name')
                    ->required()
                    ->placeholder('e.g. Kg')
                    ->columnSpanFull()
                    ->rules([
                        'required',
                        'string',
                        'min:0',
                        'max:256',
                    ])
                    ->autocomplete(false)
                    ->live(),

                Forms\Components\Select::make('related_to_unit')
                    ->options(Unit::query()->pluck('unit_name', 'id'))
                    ->required(fn (Get $get): bool => ($get('operator') != '') || ($get('related_by_value') != ''))
                    ->rules(
                        Rule::exists('units', 'id')->where('tenant_id', auth()->user()->tenant_id)
                    )
                    ->live(),

                Forms\Components\Select::make('operator')
                    ->options([
                        '*' => '(*) Multiply Operator',
                    ])
                    ->rules([
                        'max:256',
                        'min:0',
                        'string',
                    ])
                    ->required(fn (Get $get): bool => ($get('related_to_unit') != '') || ($get('related_by_value') != ''))
                    ->live(),

                Forms\Components\TextInput::make('related_by_value')
                    ->required(fn (Get $get): bool => ($get('related_to_unit') != '') || ($get('operator') != ''))
                    ->numeric()
                    ->rules([
                        'min:0',
                        'numeric',
                        'max:9999999999',
                    ])
                    ->live(),

                Forms\Components\Placeholder::make('preview')
                    ->content(function ($get) {
                        $unit_name = '1'.$get('unit_name');

                        $related_to_unit = $get('related_to_unit');
                        $operator = $get('operator');
                        $related_by_value = $get('related_by_value');

                        if ($operator != '' || $related_to_unit != '' || $related_by_value != '') {
                            $related_to_unit = Unit::find($related_to_unit)?->unit_name ?: 'Related to unit';

                            return new HtmlString("<h1 class='text-2xl font-bold text-center'>{$unit_name} = 1 {$related_to_unit} {$operator} {$related_by_value} </h1>");
                        }

                    })
                    ->columnSpan('full')
                    ->label(''),

            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Unit::query()->with('relatedTo')->latest())
            ->columns([
                Tables\Columns\TextColumn::make('unit_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('related_to_unit')
                    ->label('Related To')
                    ->getStateUsing(function ($record) {
                        return $record->relatedTo?->unit_name ?? '-';
                    }),
                Tables\Columns\TextColumn::make('operator')
                    ->label('Related Sign')
                    ->getStateUsing(function ($record) {
                        return $record->operator ?? '-';
                    }),

                Tables\Columns\TextColumn::make('related_by_value')
                    ->label('Related By')
                    ->getStateUsing(function ($record) {
                        return $record->related_by_value ?? '-';
                    }),
                Tables\Columns\TextColumn::make('result')
                    ->getStateUsing(function ($record) {
                        $relatedTo = $record->relatedTo?->unit_name;
                        if ($relatedTo != '') {
                            return $record->unit_name.' = 1 '.$relatedTo.' '.$record->operator.' '.$record->related_by_value;
                        }

                    }),

            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->size('sm')
                    ->outlined()
                    ->before(function ($record, $action) {
                        $mainUnit = Unit::where('related_to_unit', $record->id)->exists();

                        if (($record->products()->count() > 0) || $mainUnit) {
                            Notification::make()
                                ->title("You can't delete it ")
                                ->danger()
                                ->send();
                            $action->cancel();
                        }

                    }),
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
            'index' => Pages\ListUnits::route('/'),
            // 'create' => Pages\CreateUnit::route('/create'),
            // 'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
