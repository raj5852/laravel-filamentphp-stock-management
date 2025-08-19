<x-filament-panels::page>

   
   @if(!request('invoices'))
    @livewire('pos')
   @else
   @livewire('pos-invoice', ['record' => request('invoices')])
   @endif
</x-filament-panels::page>
