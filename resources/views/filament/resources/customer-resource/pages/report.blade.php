<x-filament-panels::page>

    @livewire('customer-report',  ['customerId' => $this->record->id])
    @livewire('customer-payment-report',  ['customerId' => $this->record->id])

</x-filament-panels::page>
