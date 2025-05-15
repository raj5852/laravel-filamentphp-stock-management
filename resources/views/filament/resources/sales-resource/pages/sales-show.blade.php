<x-filament-panels::page>
    @php
        $balance = 0;

        if ($customer->wallet <= 0) {
            $balance = abs($customer->wallet);
        } else {
            $balance = 0;
        }

        $previous_due = (abs($balance) + $customer->orders_sum_due ?: 0) - $order->due;

    @endphp
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
        <div class="flex justify-between items-center border-b pb-4 mb-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $setting->company_name }} </p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-700 dark:text-gray-300">Address: {{ $setting->address }}</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">Phone: {{ $setting->phone }}</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">Email: {{ $setting->email_address }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Invoice No:</span>
                    {{ $order->invoiceno }} </p>
                <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Client Name:</span>
                    {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->customer_name }} </p>
                <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Address:</span>
                    {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->address }}</p>
                <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Mobile:</span>
                    {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->phone }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Date:</span>
                    {{ $order->order_date }} </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 border-collapse">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="border px-4 py-2">#</th>
                        <th class="border px-4 py-2">Details</th>
                        <th class="border px-4 py-2">Qty</th>
                        <th class="border px-4 py-2">Price</th>
                        <th class="border px-4 py-2">Net.A</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderitems as $key => $item)
                        <tr>
                            <td class="border px-4 py-2">{{ $key + 1 }}</td>
                            <td class="border px-4 py-2">{{ $item->product->product_name }}</td>
                            <td class="border px-4 py-2">{{ $item->total_in_text }}</td>
                            <td class="border px-4 py-2">{{ $item->rate }}</td>
                            <td class="border px-4 py-2">{{ $item->total_rate }}</td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <p class="text-right text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Total:</span>
                {{ number_format($order->receivable, 2) }} Tk</p>

            <p class="text-right text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Total
                    Paid:</span> {{ number_format($order->paid, 2) }} Tk</p>

            @if ($previous_due > 0)
                <p class="text-right text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Previous
                        Due:</span> {{ number_format($previous_due, 2) }} Tk</p>
            @endif

            @if ($previous_due > 0)
                <p class="text-right text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Current
                        Due:</span> {{ number_format($order->due, 2) }} Tk</p>
            @endif

            <p class="text-right text-sm text-gray-700 dark:text-gray-300"><span class="font-semibold">Total Due:</span>
                {{ number_format($order->due + $previous_due, 2) }} Tk</p>
        </div>

        <div class="mb-6">
            <div class="mb-2" style="display: flex; justify-content: space-between">
                <div style="font-size: 25px">Payments</div>

                <x-filament::button class="" color="primary" tag="button"
                    wire:click="mountAction('addpayment', { id: {{ $order->id }} , amount: {{ $order->due ?: 0 }} })">
                    Add Payment
                </x-filament::button>
            </div>

            <div>
                <table class="w-full text-sm border border-gray-300 dark:border-gray-700">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-800">
                            <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Date
                            </th>
                            <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Amount
                            </th>
                            <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->histories as $history)
                            <tr>
                                <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                    {{ Carbon\Carbon::parse($history->date)->format('d M, Y') }} </td>
                                <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                    {{ number_format($history->amount, 2) }} </td>
                                <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                    <x-filament::button class=" text-center" color="danger" tag="button"
                                        wire:click="mountAction('delete', { id: {{ $history->id }} })">
                                        <x-fas-trash class="w-4 h-4 " />
                                    </x-filament::button>
                                </td>

                            </tr>
                        @endforeach


                    </tbody>
                </table>
            </div>

        </div>
    </div>

</x-filament-panels::page>
