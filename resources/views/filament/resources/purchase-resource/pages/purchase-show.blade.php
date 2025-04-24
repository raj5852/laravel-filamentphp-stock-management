<x-filament-panels::page>
    {{-- @dd($damages) --}}
    <div style="min-width: 70% !important" class=" mx-auto">
        <div id="invoice-container" class="p-8 bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 rounded-lg">
            <div id="invoice-container2" class="mb-6">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold"> {{ $setting['company_name'] }} </h1>
                    <p class="text-sm">
                        Address: {{ $setting['address'] }}. <br />
                        Phone: {{ $setting['phone'] }} <br />
                        Email: {{ $setting['email_address'] }}
                    </p>
                </div>
                <div class="mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p><strong>Invoice No:</strong> {{ $purchase->billno }} </p>
                            <p><strong>Supplier Name:</strong> {{ $purchase->supplier?->supplier_name }} </p>
                            <p><strong>Address:</strong>{{ $purchase->supplier?->address }} </p>
                            <p><strong>Mobile:</strong> {{ $purchase->supplier?->phone }}</p>
                        </div>
                        <div class="text-right">
                            <p><strong>Date:</strong>

                                {{ Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }} </p>
                        </div>
                    </div>
                </div>
                <div class="mb-6">
                    <table class="w-full text-sm border border-gray-300 dark:border-gray-700">
                        <thead>
                            <tr class="bg-gray-200 dark:bg-gray-800">
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">#</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Details</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">Qty</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-right">Price</th>
                                <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-right">Net.A</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->purchaseitems as $key => $item)
                                <tr>
                                    <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                        {{ $key + 1 }}
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                        {{ $item->product?->product_name }} | {{ $item->product?->product_code }}
                                    </td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">
                                        {{ $item->total_in_text }} </td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-right">
                                        {{ number_format($item->rate, 2) }} Tk</td>
                                    <td class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-right">
                                        {{ number_format($item->total_rate, 2) }} Tk</td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
                <div class="mb-6 text-right text-sm">
                    <p><strong>Grand Total:</strong> {{ number_format($purchase->payable, 2) }} Tk</p>
                    <p><strong>Paid:</strong> {{ number_format($purchase->paid, 2) }} Tk</p>
                    <p><strong>Due:</strong> {{ number_format($purchase->due, 2) }} Tk</p>
                </div>



                <div class="mb-6">
                    <div class="mb-2" style="display: flex; justify-content: space-between">
                        <div style="font-size: 25px">Payments</div>

                        <x-filament::button class="" color="primary" tag="button"
                            wire:click="mountAction('addpayment', { id: {{ $purchase->id }} , amount: {{ $purchase->due ?: 0 }} })">
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
                                @foreach ($purchase->histories as $history)
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


                <div class="mb-7">
                    <div class="mb-2" style="display: flex; justify-content: space-between">
                        <div style="font-size: 25px">Sales</div>


                    </div>

                    <div>
                        <table class="w-full text-sm border border-gray-300 dark:border-gray-700">
                            <thead>
                                <tr class="bg-gray-200 dark:bg-gray-800">
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Date
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Sale
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">Name
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">Qty
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    <tr>
                                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                            {{ Carbon\Carbon::parse($sale->date)->format('d M, Y') }} </td>
                                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1"> <a
                                                href="{{ route('filament.admin.resources.damages.index', ['tableFilters[id][id]=' => $sale->invoiceno]) }}"
                                                style="color: #33cabb">Sale#{{ $sale->order->invoiceno }} </a> </td>
                                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                            {{ $sale->product?->product_name }} </td>
                                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                            @php
                                                $purchase = collect($sale->purchase_ids)->firstWhere(
                                                    'purchase_id',
                                                    $this->record->id,
                                                );
                                            @endphp
                                            {{ $purchase ? $purchase['qty_in_text'] : 'N/A' }}
                                        </td>

                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="mb-7">
                    <div class="mb-2" style="display: flex; justify-content: space-between">
                        <div style="font-size: 25px">Returns</div>


                    </div>

                    <div>
                        <table class="w-full text-sm border border-gray-300 dark:border-gray-700">
                            <thead>
                                <tr class="bg-gray-200 dark:bg-gray-800">
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Date
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Return
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">Name
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">Qty
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>

                                </tr>


                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="mb-7">
                    <div class="mb-2" style="display: flex; justify-content: space-between">
                        <div style="font-size: 25px">Damages</div>
                    </div>

                    <div>
                        <table class="w-full text-sm border border-gray-300 dark:border-gray-700">
                            <thead>
                                <tr class="bg-gray-200 dark:bg-gray-800">
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Date
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-left">Damage
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">Name
                                    </th>
                                    <th class="border border-gray-300 dark:border-gray-700 px-2 py-1 text-center">Qty
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($damages as $damage)
                                    <tr>
                                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                            {{ Carbon\Carbon::parse($damage->damage)->format('d M, Y') }} </td>
                                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1"> <a
                                                href="{{ route('filament.admin.resources.damages.index', ['tableFilters[id][id]=' => $damage->id]) }}"
                                                style="color: #33cabb">Damage#{{ $damage->id }} </a> </td>
                                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                            {{ $damage->product?->product_name }} </td>

                                        <td class="border border-gray-300 dark:border-gray-700 px-2 py-1">
                                            @php
                                                $purchase = collect($damage->purchase_ids)->firstWhere(
                                                    'purchase_id',
                                                    $this->record->id,
                                                );
                                            @endphp
                                            {{ $purchase ? $purchase['qty_in_text'] : 'N/A' }}
                                        </td>

                                    </tr>
                                @endforeach


                            </tbody>
                        </table>
                    </div>

                </div>
            </div>



            <div class="flex justify-between print-hidden">
                <button onclick="printInvoice()"
                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700">
                    <div style="display: flex">
                        <x-fas-print class="w-5 h-5" />
                        <div style="margin-left: 5px">Print</div>
                    </div>
                </button>
                <div class="flex space-x-2">
                    <a href="{{ route('filament.admin.resources.purchases.add-purchase') }}"
                        class="px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <div style="display: flex">
                            <x-fas-reply class="w-5 h-5" />
                            <div style="margin-left: 5px">New Purchase</div>
                        </div>
                    </a>
                    <a href="{{ route('filament.admin.resources.purchases.index') }}"
                        class="px-4 py-2 bg-teal-500 text-white rounded hover:bg-teal-600 dark:bg-teal-600 dark:hover:bg-teal-700">
                        <div style="display: flex">
                            <x-fas-reply class="w-5 h-5" />
                            <div style="margin-left: 5px">Purchase List</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function printInvoice() {
            const invoice = document.getElementById('invoice-container2').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = invoice;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload(); // Restore layout
        }
    </script>

</x-filament-panels::page>
