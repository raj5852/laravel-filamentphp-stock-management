<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Tables\Actions\Action as ActionTable;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class Pos extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public $date = null;

    public $customer_id = null;

    public $product_id = null;

    public $barcode = null;

    public $selected_products = [];

    public function __construct()
    {
        $this->date = now();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('barcode')
                    ->label('')
                    ->prefixIcon('fas-barcode')
                    ->placeholder('Scan Barcode'),
                Select::make('product_id')
                    ->label('')
                    ->searchable()
                    ->reactive()
                    ->options(Product::query()->pluck('product_name', 'id'))
                    ->native(false)
                    ->placeholder('Start to write product name...')
                    ->afterStateUpdated(function ($state, $set) {
                        $product = Product::findOrFail($state);

                        $existsProducts = collect($this->selected_products)->where('id', $state)->first();
                        if ($existsProducts) {
                            Notification::make()
                                ->title('Please Increase the quantity.')
                                ->danger()
                                ->send();
                            $set('product_id', null);

                            return;
                        }

                        $this->selected_products[] = [
                            'id' => $state,
                        ];
                        $set('product_id', null);

                    }),
                DatePicker::make('date')
                    ->label('')
                    ->placeholder('Date')
                    ->native(false)
                    ->default($this->date),
                Select::make('customer_id')
                    ->label('')
                    ->placeholder('Select Customer')
                    ->searchable()
                    ->native(false)
                    ->options(Customer::query()->pluck('customer_name', 'id'))
                    ->createOptionForm([
                        TextInput::make('customer_name')
                            ->label('Name')
                            ->autocomplete(false)
                            ->rules([
                                'required',
                                'string',
                                'min:0',
                                'max:256',
                            ])
                            ->placeholder('Customer Name')
                            ->required(),

                        TextInput::make('email')
                            ->label('Email')
                            ->autocomplete(false)
                            ->email()
                            ->rules([
                                'nullable',
                                'string',
                                'min:0',
                                'max:256',
                                'email',
                            ])
                            ->placeholder('Email Address'),
                        Textarea::make('address')
                            ->label('Address')
                            ->autocomplete(false)
                            ->rules([
                                'nullable',
                                'string',
                                'min:0',
                                'max:5000',
                            ])
                            ->placeholder('Address'),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->autocomplete(false)
                            ->required()
                            ->rules([
                                'required',
                                'string',
                                'min:0',
                                'max:256',
                            ])
                            ->placeholder('Phone Number'),

                        TextInput::make('opening_receivable')
                            ->label('Opening Receivable')
                            ->autocomplete(false)
                            ->rules([
                                'nullable',
                                'numeric',
                                'min:0',
                                'max:9999999999',
                            ])
                            ->placeholder('Opening Receivable'),

                        TextInput::make('opening_payable')
                            ->label('Opening Payable')
                            ->autocomplete(false)
                            ->rules([
                                'nullable',
                                'numeric',
                                'min:0',
                                'max:9999999999',
                            ])
                            ->placeholder('Opening Payable'),

                    ])
                    ->createOptionAction(function (Action $action) {
                        $action
                            ->button()
                            ->color(Color::Green)
                            ->icon('')
                            ->size('lg')
                            ->label('Add')
                            ->modalWidth('md')
                            ->modalCancelAction(false)
                            ->modalSubmitActionLabel('Add Customer');
                    })
                    ->createOptionModalHeading('Add Customer')
                    ->createOptionUsing(function ($data) {
                        $customer = Customer::create([
                            'customer_name' => $data['customer_name'],
                            'email' => $data['email'],
                            'address' => $data['address'],
                            'phone' => $data['phone'],
                            'opening_receivable' => $data['opening_receivable'] ?: 0,
                            'opening_payable' => $data['opening_payable'] ?: 0,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Customer Added Successfully')
                            ->send();

                        return $customer->id;
                    }),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->latest()->with('productdetails:id,product_id,available_stock_in_text'))
            ->columns([
                Split::make([
                    Stack::make([
                        ImageColumn::make('product_image')->defaultImageUrl('/images/notfound.jpg')->alignCenter(),
                        TextColumn::make('product_name')->getStateUsing(fn ($record) => $record->product_name.' - '.$record->product_code)->searchable(['product_name', 'product_code'])->alignCenter(),
                        TextColumn::make('sale_price')->getStateUsing(fn ($record) => number_format($record->sale_price, 2, '.', ''))->alignCenter(),
                        TextColumn::make('productdetails.available_stock_in_text')
                            ->getStateUsing(function ($record) {
                                return new HtmlString('<span class="font-bold">Stock: </span>'.$record->productdetails?->available_stock_in_text);
                            })
                            ->alignCenter(),
                    ]),

                ]),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(Category::query()->pluck('name', 'id'))
                    ->placeholder('Select Category')
                    ->searchable(),
            ])
            ->recordAction('add_to_cart')
            ->actions([
                ActionTable::make('add_to_cart')
                    ->label('')
                    ->icon('')
                    ->action(function ($record) {
                        if (! $record) {
                            Notification::make()
                                ->title('Item not found!')
                                ->danger()
                                ->send();
                        }
                        $existsProducts = collect($this->selected_products)->where('id', $record->id)->first();
                        if ($existsProducts) {
                            Notification::make()
                                ->title('Please Increase the quantity.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $this->selected_products[] = [
                            'id' => $record->id,
                        ];
                    }),

            ])
            ->paginated([12]);
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
