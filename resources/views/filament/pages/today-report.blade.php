<x-filament-panels::page>
    @php
        $start_date = Carbon\Carbon::today();
        $end_date = Carbon\Carbon::today();
    @endphp
    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
        <div class="space-y-2">
            @livewire('receive-from-customer', [
                'startDate' => $start_date,
                'endDate' => $end_date,
            ])
        </div>

        @livewire('pay-to-supplier', [
            'startDate' => $start_date,
            'endDate' => $end_date,
        ])
    </div>

    @livewire('top-sale-product', [
        'startDate' => $start_date,
        'endDate' => $end_date,
    ])

</x-filament-panels::page>
