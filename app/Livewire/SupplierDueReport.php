<?php

namespace App\Livewire;

use App\Filament\Exports\SupplierDueReportExporter;
use App\Models\Supplier;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class SupplierDueReport extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public $supplier_id = null;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Select::make('supplier_id')
                        ->label('Supplier')
                        ->placeholder('Select Supplier')
                        ->options(Supplier::query()->where('is_default', '!=', '1')->pluck('supplier_name', 'id'))
                        ->searchable(),

                ])
                    ->columns(2),
            ]);
    }

    public function filter()
    {
        $this->supplier_id = $this->form->getState()['supplier_id'];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                // First get all suppliers with their purchases sum
                $query = Supplier::query()
                    ->when($this->supplier_id, function ($query) {
                        return $query->where('id', $this->supplier_id);
                    })
                    ->withSum('purchases', 'due');

                // Then filter the results after the query is executed
                return $query->where(function (Builder $query) {
                    $query->whereHas('purchases', function (Builder $subQuery) {
                        $subQuery->where('due', '>', 0);
                    })
                        ->orWhere('wallet', '<', 0);
                });
            })
            ->heading(new HtmlString('<h2 style="font-size:23px; font-weight:bold">Supplier Due Report</h2>'))
            ->columns([
                TextColumn::make('supplier_name')->label('Name'),
                TextColumn::make('email')->label('Email'),
                TextColumn::make('phone')->label('Phone'),
                TextColumn::make('address')
                    ->label('Address')
                    ->wrap(),
                TextColumn::make('purchases_sum_due')
                    ->label('Invoice Due')
                    ->formatStateUsing(fn ($state) => number_format($state ?? 0, 2).' TK'),
                TextColumn::make('wallet')
                    ->label('Direct Due')
                    ->formatStateUsing(fn ($state) => number_format(abs($state ?? 0), 2).' TK'),
                TextColumn::make('total_dues')
                    ->label('Total Due')
                    ->getStateUsing(function ($record) {
                        return abs($record->purchases_sum_due ?? 0) + abs($record->wallet ?? 0);
                    })
                    ->formatStateUsing(fn ($state) => number_format($state, 2).' TK'),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export')
                    ->icon('fas-download')
                    ->columnMapping(false)
                    ->modalHeading('Export Supplier Due Report')
                    ->exporter(SupplierDueReportExporter::class)
                    ->modifyQueryUsing(function (Builder $query) {
                        return $query->when($this->supplier_id, function ($query) {
                            return $query->where('id', $this->supplier_id);
                        })
                            ->withSum('purchases', 'due')
                            ->where(function (Builder $query) {
                                $query->whereHas('purchases', function (Builder $subQuery) {
                                    $subQuery->where('due', '>', 0);
                                })
                                    ->orWhere('wallet', '<', 0);
                            });
                    }),
            ])
            ->filters([
                SelectFilter::make('id')
                    ->label(' ')
                    ->placeholder('Select Supplier')
                    ->options(Supplier::query()->where('is_default', '!=', '1')->pluck('supplier_name', 'id'))
                    ->searchable(),
            ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(2)
            ->paginated([10, 25, 50, 100]);
    }

    public function render()
    {
        return view('livewire.supplier-due-report');
    }
}
