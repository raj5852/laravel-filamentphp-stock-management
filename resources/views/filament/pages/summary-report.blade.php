<x-filament-panels::page>


    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">

        <div class="space-y-2">
            @livewire('top-sale-product', [
                'isFilter' => false,
            ])
        </div>

        @livewire('expense-report', [
            'isFilter' => false,
        ])
    </div>


    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
        <div class="space-y-2">


            @livewire('pay-to-supplier', [
                'isFilter' => false,
            ])
        </div>

        @livewire('receive-from-customer', [
            'isFilter' => false,
        ])


    </div>

</x-filament-panels::page>
