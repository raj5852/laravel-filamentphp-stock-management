<?php

namespace App\Filament\Resources;

use App\Filament\Exports\ProductExporter;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Product Information';

    protected static ?string $navigationIcon = 'fab-product-hunt';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Group::make()->schema([
                    Section::make()->schema([
                        Forms\Components\TextInput::make('product_name')
                            ->label('Product Name')
                            ->rules([
                                'required',
                                'string',
                                'min:0',
                                'max:256',
                            ])
                            ->autocomplete(false)
                            ->placeholder('Product Name')
                            ->required(),

                        Forms\Components\TextInput::make('product_code')
                            ->placeholder('Product Code')
                            ->autocomplete(false)
                            ->rules([
                                'string',
                                'max:50',
                                'regex:/^[a-zA-Z0-9]+$/', // Only allow alphanumeric characters (English letters and numbers)
                            ])
                            ->validationMessages([
                                'regex' => 'Product code must contain only English letters and numbers',
                            ])
                            ->readOnly(fn(string $context) => $context === 'edit')
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn($rule) => $rule->where('tenant_id', auth()->user()->tenant_id)),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->placeholder('Select Category')
                            ->required()
                            ->options(Category::query()->get(['id', 'name'])->pluck('name', 'id'))
                            ->searchable()
                            ->rules([
                                Rule::exists('categories', 'id')->where(function ($query) {
                                    $query->where('tenant_id', auth()->user()->tenant_id);
                                }),
                                'required',
                            ])
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Category Name')
                                    ->autocomplete(false)
                                    ->rules([
                                        'required',
                                        'string',
                                        'min:0',
                                        'max:256',
                                    ])
                                    ->placeholder('Category Name')
                                    ->required(),
                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                $action
                                    ->button()
                                    ->outlined()
                                    ->color(Color::Green)
                                    ->modalWidth('md')
                                    ->modalCancelAction(false)
                                    ->label('Add Category');
                            })
                            ->createOptionModalHeading('Create a new Category')
                            ->createOptionUsing(function ($data) {
                                $category = Category::create([
                                    'name' => $data['name'],
                                ]);
                                Notification::make()
                                    ->title('Category Created')
                                    ->body('The Category has been successfully added.')
                                    ->success()
                                    ->send();

                                return $category->id;
                            }),

                        Forms\Components\Select::make('brand_id')
                            ->label('Brand')
                            ->options(Brand::query()->get(['id', 'brand_name'])->pluck('brand_name', 'id'))
                            ->placeholder('Select Brand')
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('brand_name')
                                    ->autocomplete(false)
                                    ->rules([
                                        'required',
                                        'string',
                                        'min:0',
                                        'max:256',
                                    ])
                                    ->placeholder('Brand Name')
                                    ->required(),
                            ])
                            ->createOptionModalHeading('Create a new Brand')
                            ->rules([
                                Rule::exists('brands', 'id')->where(function ($query) {
                                    $query->where('tenant_id', auth()->user()->tenant_id);
                                }),
                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                $action
                                    ->button()
                                    ->outlined()
                                    ->color(Color::Green)
                                    ->label('Add Brand')
                                    ->modalCancelAction(false)
                                    ->modalWidth('md');
                            })
                            ->createOptionUsing(function ($data) { // This function creates a new brand
                                $brand = Brand::create([
                                    'brand_name' => $data['brand_name'],
                                ]);
                                Notification::make()
                                    ->title('Brand Created')
                                    ->body('The brand has been successfully added.')
                                    ->success()
                                    ->send();

                                return $brand->id;
                            }),

                        Forms\Components\Select::make('unit_id')
                            ->label('Main Unit')
                            ->required()
                            ->options(Unit::query()->pluck('unit_name', 'id'))
                            ->searchable()
                            ->placeholder('Select Main Unit')
                            ->live()
                            ->reactive()
                            ->rules([
                                Rule::exists('units', 'id')->where(function ($query) {
                                    $query->where('tenant_id', auth()->user()->tenant_id);
                                }),
                                'required',
                            ])
                            ->hidden(fn(string $context) => $context === 'edit')
                            ->afterStateUpdated(function ($set) {
                                $set('sub_unit', null);
                                $set('first_opening_stock', null);
                                $set('second_opening_stock', null);
                            }),

                        Forms\Components\Select::make('sub_unit')
                            ->live()
                            ->placeholder('Select Sub Unit')
                            ->rules([
                                Rule::exists('units', 'id')->where(function ($query) {
                                    $query->where('tenant_id', auth()->user()->tenant_id);
                                }),
                            ])
                            ->options(function ($get) {
                                $subunit = Unit::find($get('unit_id'))?->relatedTo;
                                if ($subunit == null) {
                                    return [];
                                } else {
                                    return [
                                        $subunit?->id ?? null => $subunit?->unit_name ?? null,
                                    ];
                                }
                            })
                            ->searchable()
                            ->reactive()
                            ->hidden(fn(string $context) => $context === 'edit')
                            ->afterStateUpdated(function ($set) {
                                $set('first_opening_stock', null);
                                $set('second_opening_stock', null);
                            }),

                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('first_opening_stock')
                                ->minValue(0)
                                ->columnSpan(function ($get) {
                                    if ($get('sub_unit') != '') {
                                        return 1;
                                    } else {
                                        return 2;
                                    }
                                })
                                ->label('Opening Stock')
                                ->placeholder(function ($get) {
                                    $subunit = Unit::find($get('unit_id'));

                                    return $subunit?->unit_name ?? '';
                                })
                                ->rules([
                                    'integer',
                                    'min:0',
                                    'max_digits:10',
                                ])
                                ->numeric(),

                            Forms\Components\TextInput::make('second_opening_stock')
                                ->label('Sub Opening Stock')
                                ->visible(function ($get) {
                                    if ($get('sub_unit') != '') {
                                        return true;
                                    }
                                })
                                ->placeholder(function ($get) {
                                    $subunit = Unit::find($get('sub_unit'));

                                    return $subunit?->unit_name;
                                })
                                ->minValue(0)
                                ->rules([
                                    'min:0',
                                    'max_digits:10',
                                    'integer',

                                ])
                                ->numeric(),
                        ])->hidden(fn(string $context) => $context === 'edit'),

                    ]),
                ])->columnSpan(['lg' => 2]),

                Group::make()->schema([
                    Section::make()->schema([

                        Forms\Components\TextInput::make('sale_price')
                            ->label('Sale Price')
                            ->placeholder('Sale Price')
                            ->rules([
                                'required',
                                'numeric',
                                'min:0',
                                'max:9999999999',
                            ])
                            ->minValue(0)
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('purchase_cost')
                            ->label('Purchase Cost')
                            ->placeholder('Purchase Cost')
                            ->required()
                            ->minValue(0)
                            ->rules([
                                'required',
                                'numeric',
                                'min:0',
                                'max:9999999999',
                            ])
                            ->numeric(),
                        Forms\Components\Textarea::make('product_details')
                            ->rules([
                                'string',
                                'max:5000',
                            ])
                            ->placeholder('Product Details'),

                        Forms\Components\FileUpload::make('product_image')
                            ->image()
                            ->imageEditor()
                            ->rules([
                                'image',
                            ])
                            ->optimize('jpg')
                            ->resize(50),

                    ]),
                ])
                    ->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->with('category')->latest())
            ->columns([
                Tables\Columns\ImageColumn::make('product_image')
                    ->label('Image')
                    ->defaultImageUrl('/images/notfound.jpg'),

                Tables\Columns\TextColumn::make('product_code')
                    ->label('Code'),

                Tables\Columns\TextColumn::make('product_name')
                    ->extraAttributes(['class' => 'max-w-[250px] whitespace-normal'])
                    ->label('Name'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category'),

                Tables\Columns\TextColumn::make('brand.brand_name')
                    ->label('Brand'),

                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Price')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', '')),

                Tables\Columns\TextColumn::make('purchase_cost')
                    ->label('Cost')
                    ->formatStateUsing(fn($state) => number_format((float) $state, 2, '.', '')),

                Tables\Columns\TextColumn::make('product_details')
                    ->label('Details'),

            ])
            ->filters([
                Filter::make('product_code')
                    ->label('')
                    ->form([
                        TextInput::make('product_code')
                            ->label('')
                            ->autocomplete(false)
                            ->placeholder('Enter Product Code'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['product_code'],
                            fn($query, $term) => $query->where('product_code', 'like', '%' . $term . '%'),
                        );
                    }),
                Filter::make('product_name')
                    ->label('')
                    ->form([
                        TextInput::make('product_name')
                            ->label('')
                            ->autocomplete(false)
                            ->placeholder('Enter Product Name'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['product_name'],
                            fn($query, $term) => $query->where('product_name', 'like', '%' . $term . '%'),
                        );
                    }),
                SelectFilter::make('category_id')
                    ->label(' ')
                    ->placeholder('Select Category')
                    ->options(Category::query()->pluck('name', 'id'))
                    ->searchable(),
                SelectFilter::make('brand_id')
                    ->label(' ')
                    ->placeholder('Select Brand')
                    ->options(Brand::query()->pluck('brand_name', 'id'))
                    ->searchable(),


                Filter::make('product_details')
                    ->label('')
                    ->form([
                        TextInput::make('product_details')
                            ->label('')
                            ->autocomplete(false)
                            ->placeholder('Enter Product Details'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['product_details'],
                            fn($query, $term) => $query->where('product_details', 'like', '%' . $term . '%'),
                        );
                    }),

            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->hiddenFilterIndicators()
            ->headerActions([
                ExportAction::make()
                    ->label('Export')
                    ->icon('fas-download')
                    ->columnMapping(false)
                    ->exporter(ProductExporter::class)
                    ->modifyQueryUsing(function (Builder $query) {
                        return $query->with(['category', 'brand', 'unit', 'subunit', 'productdetails']);
                    })


            ])
            ->recordAction('print_qr')
            ->actions([

                Action::make('view')
                    ->label('')
                    ->icon('fas-eye')
                    ->button()
                    ->outlined()
                    ->modalHeading(fn($record) => $record->product_name) // Dynamic title
                    ->modalContent(fn($record) => view('filament.modals.product-details', ['product' => $record->load('category', 'brand', 'productdetails')]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Action::make('sell-history')
                        ->label('Sell History')
                        ->icon('heroicon-s-clock')
                        // clock-rotate-left
                        ->url(fn(Product $record) => route('filament.admin.resources.products.sell-history', ['record' => $record->id])),
                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record, $action) {

                            $damage = $record->damages()->exists();
                            $productid = $record->id;

                            $purchase = Purchase::where('is_purchase', 1)
                                ->whereHas('purchaseitems', function ($query) use ($productid) {
                                    $query->where('product_id', $productid);
                                })->exists();

                            $record->purchaseitems()->exists();
                            $orderitems = $record->orderitems()->exists();
                            if ($damage || $purchase || $orderitems) {
                                Notification::make()
                                    ->title("You can't delete it.")
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),

                    // sell-history

                ])
                    ->dropdown(true)
                    ->label('Actions')
                    ->button()
                    ->size('sm')
                    ->icon('fas-gears'),

                Action::make('print_qr')
                    ->label('')
                    ->icon('fas-qrcode')
                    ->button()
                    ->outlined()
                    ->modalContent(fn($record) => view('filament.modals.qr-code', [
                        'qrCode' => QrCode::size(200)->generate($record->product_code),
                        'record' => $record,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('sm'),

                Action::make('barcode')
                    ->label('')
                    ->icon('fas-barcode')
                    ->button()
                    ->outlined()
                    ->modalContent(fn($record) => view('filament.modals.barcode', [
                        'record' => $record,
                        'company_name' => Setting::first()->company_name,
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('sm'),

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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'sell-history' => Pages\Sell::route('/sell-history/{record}'),
            // 'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
