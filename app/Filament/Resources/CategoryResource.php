<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationGroup = 'Product Information';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->placeholder('Category Name')
                    ->rules([
                        'required',
                        'string',
                        'min:0',
                        'max:256',
                    ])
                    ->autocomplete(false)
                    ->required(),

                FileUpload::make('image')
                    ->image()
                    ->imageEditor()
                    ->visibility('public')
                    ->rules(['image'])
                    ->optimize('webp')
                    ->imagePreviewHeight('180')
                    ->maxSize(5120) // 5 MB
                    ->resize(85),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Category::query()->latest()->withCount('products'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\ImageColumn::make('image')
                    ->defaultImageUrl(url('/images/notfound.jpg'))
                    ->extraImgAttributes(['loading' => 'lazy']),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Count Products'),
                //

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
            'index' => Pages\ListCategories::route('/'),
            // 'create' => Pages\CreateCategory::route('/create'),
            // 'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
