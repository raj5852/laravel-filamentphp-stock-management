<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationGroup = 'Product Information';

    protected static ?string $navigationIcon = 'fas-award';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('brand_name')
                    ->autocomplete(false)
                    ->placeholder('Brand Name')
                    ->rules([
                        'required',
                        'string',
                        'min:0',
                        'max:256',
                    ])
                    ->required(),

                FileUpload::make('brand_logo')
                    ->imageEditor()
                    ->visibility('public')
                    ->rules(['image'])
                    ->optimize('webp')
                    ->imagePreviewHeight('180')
                    ->maxSize(5120) // 5 MB
                    ->resize(85),

                Forms\Components\Textarea::make('brand_description')
                    ->placeholder('Brand Description')
                    ->rules([
                        'string',
                        'min:0',
                        'max:5000',

                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Brand::query()->withCount('products'))
            ->columns([
                Tables\Columns\TextColumn::make('brand_name')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('brand_logo')
                    ->defaultImageUrl(url('/images/notfound.jpg'))
                    ->extraImgAttributes(['loading' => 'lazy']),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Count Products'),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record, $action) {

                        if ($record->products()->exists()) {
                            Notification::make()
                                ->title("You can't delete it because it has products")
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
            'index' => Pages\ListBrands::route('/'),
            // 'create' => Pages\CreateBrand::route('/create'),
            // 'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
