<?php

namespace App\Filament\Superadmin\Resources\UserResource\Pages;

use App\Filament\Superadmin\Resources\UserResource;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class Setting extends Page
{
    use InteractsWithRecord;

    protected static string $resource = UserResource::class;

    protected static string $view = 'filament.superadmin.resources.user-resource.pages.setting';

    public $oversale;
    public $is_customer_group;

    public function mount(User $record): void
    {
        $this->record = $record;
        $settings = DB::table('settings')->where('tenant_id', $record->id)->first();
        $this->oversale = $settings->oversale ?? false;
        $this->is_customer_group = $settings->is_customer_group ?? false;
    }

    public function getRecord(): User
    {
        return $this->record;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('oversale')
                    ->options([
                        0 => 'No',
                        1 => 'Yes',
                    ]),

                    Select::make('is_customer_group')
                        ->options([
                            0 => 'No',
                            1 => 'Yes',
                        ]),

            ]);
    }

    public function save(): void
    {
        DB::table('settings')->where('tenant_id', $this->record->id)->update([
            'oversale' => $this->oversale,
            'is_customer_group' => $this->is_customer_group,
        ]);

        Notification::make()
            ->title('Settings saved successfully')
            ->success()
            ->send();
    }
}
