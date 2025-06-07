<x-filament-panels::page>
    @php
        $start_date = Carbon\Carbon::now()->startOfMonth();
        $end_date = Carbon\Carbon::now()->endOfMonth();
    @endphp
    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">

        <div class="space-y-2">
            @livewire('top-sale-product', [
                'startDate' => $start_date,
                'endDate' => $end_date,
            ])
        </div>

        @livewire('expense-report', [
            'startDate' => $start_date,
            'endDate' => $end_date,
        ])
    </div>


    <div class="grid gap-4 grid-cols-1 md:grid-cols-2">
        <div class="space-y-2">


            @livewire('pay-to-supplier', [
                'startDate' => $start_date,
                'endDate' => $end_date,
            ])
        </div>

        @livewire('receive-from-customer', [
            'startDate' => $start_date,
            'endDate' => $end_date,
        ])


    </div>

</x-filament-panels::page>
