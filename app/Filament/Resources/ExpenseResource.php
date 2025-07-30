<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'fas-money-bill-trend-up';

    protected static ?string $navigationGroup = 'Expenses & Payment';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->rules([
                            'string',
                            'min:0',
                            'max:255',
                        ])
                        ->autocomplete(false)
                        ->placeholder('Expense Name')
                        ->label('Expense Name')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('amount')
                        ->required()
                        ->rules([
                            'numeric',
                            'min:0',
                            'max:9999999999999999',
                            'required',
                        ])
                        ->label('Expense Amount')
                        ->placeholder('Expense Amount')
                        ->numeric(),
                    Forms\Components\DatePicker::make('date')
                        ->rules([
                            'required',
                            'date',
                        ])
                        ->label('Expense Date')
                        ->placeholder('Expense Date')
                        ->default(today())
                        ->native(false)
                        ->required(),
                    Select::make('expense_category_id')
                        ->required()
                        ->label('Expense Category:')
                        ->options(ExpenseCategory::query()->pluck('name', 'id'))
                        ->rules([
                            'required',
                            'exists:expense_categories,id',
                        ])
                        ->searchable(),
                    Select::make('account_id')
                        ->options(Account::query()->where('is_active', true)->pluck('name', 'id'))
                        ->required()
                        ->label('Transaction Account')
                        ->rules([
                            'required',
                            'exists:accounts,id',
                        ])
                        ->searchable(),
                    Forms\Components\Textarea::make('note')
                        ->rules([
                            'max:65535',
                        ])
                        ->placeholder('Enter Optional note')
                        ->label('Expense Note'),

                ])->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Expense')
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expenseCategory.name')
                    ->label('Category'),

                Tables\Columns\TextColumn::make('amount')
                    ->getStateUsing(function ($record) {
                        return number_format($record->amount, 2).' Tk';
                    })
                    ->numeric()
                    ->summarize(
                        Sum::make()->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', '').' Tk')->label('Total')
                    ),

                Tables\Columns\TextColumn::make('note')
                    ->label('Note'),
            ])
            ->filters([
                Filter::make('start_date')
                    ->label('')
                    ->form([
                        DatePicker::make('start_date')
                            ->label('')
                            ->native(false)
                            ->placeholder('Start Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['start_date'],
                            fn ($query, $term) => $query->where('date', '>=', $term)
                        );
                    }),
                Filter::make('end_date')
                    ->label('')
                    ->form([
                        DatePicker::make('end_date')
                            ->label('')
                            ->native(false)
                            ->placeholder('End Date'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['end_date'],
                            fn ($query, $term) => $query->where('date', '<=', $term)
                        );
                    }),
                SelectFilter::make('expense_category_id')
                    ->label('')
                    ->placeholder('Expense Category')
                    ->options(ExpenseCategory::query()->pluck('name', 'id'))

                    ->searchable(),

            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()->button(),
                DeleteAction::make()->button(),
            ])
            ->filtersFormColumns(3)

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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
