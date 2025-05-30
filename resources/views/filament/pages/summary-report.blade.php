<x-filament-panels::page>

    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
        <div class="space-y-2">
            @livewire('receive-from-customer', [
                'isFilter' => false,
            ])
        </div>

        @livewire('pay-to-supplier', [
            'isFilter' => false,
        ])
    </div>

    @livewire('top-sale-product', [
        'isFilter' => false,
    ])
</x-filament-panels::page>
