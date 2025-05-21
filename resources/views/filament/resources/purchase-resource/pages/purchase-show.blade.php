<x-filament-panels::page>
    <div class="max-w-4xl mx-auto p-1 md:p-6">
        <div class="bg-white shadow-md rounded-sm p-1 md:p-6">
            <div id="invoice-container">
                <!-- Company info -->
                <div class="flex flex-col md:flex-row justify-center md:justify-between mb-6">
                    <div class="flex flex-col items-center">
                        {{-- <div class="bg-[#3498db] text-white px-3 py-1 mb-1">
                        <span class="font-bold">SOFT</span>
                        <span class="bg-white text-[#3498db] px-2 py-0.5 font-bold">GHOR</span>
                    </div>
                    <p class="text-xs text-gray-600 !text-black">Digital Solution Provider</p> --}}
                        <h2 class="font-bold mt-1 !text-black">{{ $setting['company_name'] }}</h2>
                    </div>
                    <div class="md:max-w-[250px] text-center md:text-left">
                        <p class="text-sm !text-black">
                            <span class="font-semibold">Address :</span> {{ $setting['address'] }}
                        </p>
                        <p class="text-sm !text-black">
                            <span class="font-semibold">Phone :</span> {{ $setting['phone'] }}
                        </p>
                        <p class="text-sm !text-black">
                            <span class="font-semibold">Email :</span> {{ $setting['email_address'] }}
                        </p>
                    </div>
                </div>

                <!-- Invoice details -->
                <div class="border border-gray-200 mb-6">
                    <div class="grid grid-cols-2 border-b border-gray-200">
                        <div class="p-2 border-gray-200 !text-black">
                            <span class="font-semibold">Invoice No: {{ $purchase->billno }}</span>
                        </div>
                        <div class="p-2 !text-black">
                            <span class="font-semibold">Date:
                                {{ Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 border-b border-gray-200">
                        <div class="p-2 border-gray-200 !text-black">
                            <span class="font-semibold">Supplier Name :</span> {{ $purchase->supplier?->supplier_name }}
                        </div>
                        <div class="p-2"></div>
                    </div>
                    <div class="grid grid-cols-2 border-b border-gray-200">
                        <div class="p-2 border-gray-200 !text-black">
                            <span class="font-semibold">Address :</span> {{ $purchase->supplier?->address }}
                        </div>
                        <div class="p-2"></div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div class="p-2 border-gray-200 !text-black">
                            <span class="font-semibold">Mobile :</span> {{ $purchase->supplier?->phone }}
                        </div>
                        <div class="p-2"></div>
                    </div>
                </div>

                <!-- Invoice table -->
                <div class="mb-7">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100 !text-black">
                                <th class="border border-gray-200 p-2 text-left w-12 !text-black">#</th>
                                <th class="border border-gray-200 p-2 text-left !text-black">Details</th>
                                <th class="border border-gray-200 p-2 text-center !text-black">Qty</th>
                                <th class="border border-gray-200 p-2 text-right !text-black">Price</th>
                                <th class="border border-gray-200 p-2 text-right !text-black">Net A</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->purchaseitems as $key => $item)
                                <tr>
                                    <td class="border border-gray-200 p-2 text-center !text-black"> {{ $key + 1 }}
                                    </td>
                                    <td class="border border-gray-200 p-2 !text-black">
                                        {{ $item->product?->product_name }}
                                        | {{ $item->product?->product_code }}</td>
                                    <td class="border border-gray-200 p-2 text-center !text-black">
                                        {{ $item->total_in_text }}</td>
                                    <td class="border border-gray-200 p-2 text-right !text-black">
                                        {{ number_format($item->rate, 2) }} Tk</td>
                                    <td class="border border-gray-200 p-2 text-right !text-black">
                                        {{ number_format($item->total_rate, 2) }} Tk</td>
                                </tr>
                            @endforeach


                            <tr>
                                <td colspan="3" class="border border-gray-200"></td>
                                <td
                                    class="whitespace-nowrap border border-gray-200 p-2 text-right font-semibold !text-black">
                                    Grand Total :</td>
                                <td class="whitespace-nowrap border border-gray-200 p-2 text-right !text-black">
                                    {{ number_format($purchase->payable, 2) }} Tk</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="border border-gray-200"></td>
                                <td class="border border-gray-200 p-2 text-right font-semibold !text-black">Paid :</td>
                                <td class="border border-gray-200 p-2 text-right !text-black">
                                    {{ number_format($purchase->paid, 2) }} Tk</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="border border-gray-200"></td>
                                <td class="border border-gray-200 p-2 text-right font-semibold !text-black">Due :</td>
                                <td class="border border-gray-200 p-2 text-right !text-black">
                                    {{ number_format($purchase->due, 2) }} Tk</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <div class="mb-2 " style="display: flex; justify-content: space-between">
                        <div style="font-size: 25px">Payments</div>

                        <x-filament::button class="" color="primary" tag="button"
                            wire:click="mountAction('addpayment', { id: {{ $purchase->id }} , amount: {{ $purchase->due ?: 0 }} })">
                            Add Payment
                        </x-filament::button>
                    </div>
                    <div class="mb-6">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 !text-black">
                                    <th class="border border-gray-200 p-2 text-left !text-black">Date</th>
                                    <th class="border border-gray-200 p-2 text-left !text-black">Amount</th>
                                    <th class="border border-gray-200 p-2 text-center !text-black">Actions</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchase->histories as $history)
                                    <tr>
                                        <td class="border border-gray-200 p-1 text-center !text-black">
                                            {{ Carbon\Carbon::parse($history->date)->format('d M, Y') }}
                                        </td>
                                        <td class="border border-gray-200 p-1 !text-black">
                                            {{ number_format($history->amount, 2) }}</td>
                                        <td class="border border-gray-200 p-1 text-center !text-black">
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

                <div>
                    <div class="mb-2 " style="display: flex; justify-content: space-between">
                        <div style="font-size: 25px">Sales</div>


                    </div>
                    <div class="mb-6">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 !text-black">
                                    <th class="border border-gray-200 p-2 text-left !text-black">Date</th>
                                    <th class="border border-gray-200 p-2 text-left !text-black">Sale</th>
                                    <th class="border border-gray-200 p-2 text-center !text-black">Name</th>
                                    <th class="border border-gray-200 p-2 text-center !text-black">Qty</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sales as $sale)
                                    <tr>
                                        <td class="border border-gray-200 p-1 text-center !text-black">
                                            {{ Carbon\Carbon::parse($sale->date)->format('d M, Y') }}
                                        </td>
                                        <td class="border border-gray-200 p-1 !text-black">
                                            <a href="{{ route('filament.admin.resources.sales.index') }}?invoiceno={{ $sale->order->invoiceno }}"
                                                style="color: #33cabb">Sale#{{ $sale->order->invoiceno }} </a>
                                        </td>

                                        <td class="border border-gray-200 p-1 !text-black">
                                            {{ $sale->product?->product_name }}
                                        </td>
                                        <td class="border border-gray-200 p-1 !text-black">
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

                <div>
                    <div class="mb-2 " style="display: flex; justify-content: space-between">
                        <div style="font-size: 25px">Damages</div>


                    </div>
                    <div class="mb-6">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 !text-black">
                                    <th class="border border-gray-200 p-2 text-left !text-black">Date</th>
                                    <th class="border border-gray-200 p-2 text-left !text-black">Damage</th>
                                    <th class="border border-gray-200 p-2 text-center !text-black">Name</th>
                                    <th class="border border-gray-200 p-2 text-center !text-black">Qty</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($damages as $damage)
                                    <tr>
                                        <td class="border border-gray-200 p-1 text-center !text-black">
                                            {{ Carbon\Carbon::parse($damage->damage)->format('d M, Y') }}
                                        </td>
                                        <td class="border border-gray-200 p-1 !text-black">
                                            <a href="{{ route('filament.admin.resources.damages.index', ['tableFilters[id][id]=' => $damage->id]) }}"
                                                style="color: #33cabb">Damage#{{ $damage->id }} </a>
                                        </td>

                                        <td class="border border-gray-200 p-1 !text-black">
                                            {{ $damage->product?->product_name }}
                                        </td>
                                        <td class="border border-gray-200 p-1 !text-black">
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

            <br>
            <!-- Action buttons -->
            <div class="hiddenButtons">

                <div class="mb-6">
                    <button onclick="printInvoice()"
                        class="w-full bg-gray-200 text-gray-800 py-2 flex items-center justify-center gap-2 hover:bg-gray-300 !text-black">
                        {{-- <span>Print</span> --}}
                        <div style="display: flex">
                            <x-fas-print class="w-4 h-4" />
                            <div style="margin-left: 5px; margin-top: -4px">Print</div>
                        </div>
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('filament.admin.resources.purchases.add-purchase') }}"
                        class="bg-teal-500 text-white py-2 flex items-center justify-center gap-2 hover:bg-teal-600 !text-black">
                        <div style="display: flex">
                            <x-fas-reply class="w-5 h-5" />
                            <div style="margin-left: 5px">New Purchase</div>
                        </div>
                    </a>
                    <a href="{{ route('filament.admin.resources.purchases.index') }}"
                        class="bg-teal-500 text-white py-2 flex items-center justify-center gap-2 hover:bg-teal-600 !text-black">
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
            const invoice = document.getElementById('invoice-container').innerHTML;
            const originalContent = document.body.innerHTML;

            document.body.innerHTML = invoice;
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload(); // Restore layout
        }
    </script>




</x-filament-panels::page>
