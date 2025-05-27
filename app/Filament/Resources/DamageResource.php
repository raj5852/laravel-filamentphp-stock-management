<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DamageResource\Pages;
use App\Models\Damage;
use App\Models\Product;
use App\Services\ExpensePurchase;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;

class DamageResource extends Resource
{
    protected static ?string $model = Damage::class;

    protected static ?string $navigationIcon = 'fas-circle-exclamation';

    protected static ?string $navigationGroup = 'Sale & Purchase';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('test')
                    ->label('Add Damage')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('product_id')
                            ->options(Product::query()->pluck('product_name', 'id'))
                            ->label('Select a Product')
                            ->searchable()
                            ->live()
                            ->rules([
                                'required',
                                Rule::exists('products', 'id')->where('tenant_id', auth()->user()->tenant_id),
                            ])
                            ->afterStateUpdated(function ($set, $get, $state) {
                                $product = Product::find($state);
                                $set('main_unit_name', $product?->unit?->unit_name);
                                $set('sub_unit_name', $product?->subunit?->unit_name ?: null);

                                $set('available_stock_in_text', $product?->productdetails?->available_stock_in_text);
                            })
                            ->required(),

                        Placeholder::make('product_name')
                            ->label('Available Stock')
                            ->content(function ($get) {
                                if ($get('available_stock_in_text') != '') {
                                    return new HtmlString('<h1 style="color:green; font-weight: bold; font-size: 20px">'.$get('available_stock_in_text').'<h1>');
                                }
                            })->hidden(fn ($get) => $get('available_stock_in_text') == ''),
                        TextInput::make('quantity_in_main_unit')
                            ->label(function ($get) {
                                if ($get('main_unit_name') == '') {
                                    return;
                                }

                                return 'Damage Quantity ( '.$get('main_unit_name').' )';
                            })
                            ->minValue(0)
                            ->placeholder(function ($get) {
                                if ($get('main_unit_name') == '') {
                                    return;
                                }

                                return $get('main_unit_name');
                            })
                            ->hidden(fn ($get) => $get('main_unit_name') == '')
                            ->live()
                            ->rules([
                                'integer',
                                'min:0',
                                'max_digits:10',
                            ])
                            ->numeric(),

                        TextInput::make('quantity_in_sub_unit')
                            ->label(function ($get) {
                                if ($get('sub_unit_name') == '') {
                                    return;
                                }

                                return 'Damage Quantity ( '.$get('sub_unit_name').' )';
                            })
                            ->minValue(0)
                            ->placeholder(function ($get) {
                                if ($get('sub_unit_name') == '') {
                                    return;
                                }

                                return $get('sub_unit_name');
                            })
                            ->hidden(fn ($get) => $get('sub_unit_name') == '')
                            ->live()
                            ->rules([
                                'integer',
                                'min:0',
                                'max_digits:10',
                            ])
                            ->numeric(),

                        DatePicker::make('date')
                            ->native(false)
                            ->default(now())
                            ->required(),

                        Textarea::make('note')
                            ->columnSpanFull(),

                    ])
                    ->before(function (array $data, $action) {

                        $product = Product::find($data['product_id']);
                        $productQty = $product->productdetails->available_stock;

                        $getQty = getTotalStock($data['product_id'], $data['quantity_in_main_unit'] ?? null, $data['quantity_in_sub_unit'] ?? null);
                        $total_in_text = getTotalStockInText($data['product_id'], $getQty);

                        if ($getQty == 0) {
                            Notification::make()
                                ->title("Damage Quantity can't be 0")
                                ->danger()
                                ->send();
                            $action->halt();
                        }

                        if ($getQty > $productQty) {
                            Notification::make()
                                ->title('Damage Quantity is greater than Available Stock')
                                ->danger()
                                ->send();
                            $action->halt();
                        }
                        try {
                            DB::beginTransaction();

                            $purchaseIds = ExpensePurchase::addPurchaseExpense($data['product_id'], $getQty);
                            Damage::create([
                                'product_id' => $data['product_id'],
                                'quantity_in_main_unit' => $data['quantity_in_main_unit'] ?? null,
                                'quantity_in_sub_unit' => $data['quantity_in_sub_unit'] ?? null,
                                'date' => $data['date'],
                                'note' => $data['note'],
                                'total_qty' => $getQty,
                                'total_in_text' => $total_in_text,
                                'purchase_ids' => $purchaseIds,
                            ]);

                            Notification::make()
                                ->title('Damage Added Successfully')
                                ->success()
                                ->send();

                            $getDamage = $product->productdetails->damaged;
                            $product->productdetails()->increment('damaged', $getQty);

                            $product->productdetails()->update([
                                'available_stock' => $productQty - $getQty,
                                'available_stock_in_text' => getTotalStockInText($product->id, $productQty - $getQty),
                                'damaged_in_text' => getTotalStockInText($product->id, $getDamage + $getQty),
                            ]);

                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();

                            // Handle exception

                        }
                    }),
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),

                Tables\Columns\TextColumn::make('product.product_name')
                    ->extraAttributes(['class' => 'max-w-[250px] whitespace-normal']),
                Tables\Columns\TextColumn::make('date')
                    ->date(),
                Tables\Columns\TextColumn::make('total_in_text'),
                Tables\Columns\TextColumn::make('note'),
            ])
            ->filters([
                Filter::make('product_id')
                    ->label('')
                    ->form([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::query()->pluck('product_name', 'id'))
                            ->placeholder('Select Product')
                            ->searchable(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when($data['product_id'], fn ($query, $term) => $query->where('product_id', $term));
                    }),

                Filter::make('id')
                    ->label('')
                    ->form([
                        TextInput::make('id')
                            ->label('Id')
                            ->autocomplete(false)
                            ->placeholder('Id'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['id'],
                            fn ($query, $term) => $query->where('id', $term)
                        );
                    }),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->outlined(),
            ])
            ->filtersFormColumns(4)
            ->hiddenFilterIndicators()
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
            'index' => Pages\ListDamages::route('/'),
            // 'create' => Pages\CreateDamage::route('/create'),
            // 'edit' => Pages\EditDamage::route('/{record}/edit'),
        ];
    }
}
