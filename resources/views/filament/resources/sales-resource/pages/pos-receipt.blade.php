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


    <div class="max-w-[700px] w-full mx-auto p-1 md:p-6 ">
        <div class="bg-white shadow-md rounded-sm p-1 md:p-6">
            <div id="invoice-container">

                <!-- Company info -->
                <div class="flex flex-col md:flex-row justify-center md:justify-between mb-6">
                    <div class="flex flex-col items-center">
                        @if (
                            $setting->invoice_logo_type == App\Enums\InvoiceLogoType::LOGO ||
                                $setting->invoice_logo_type == App\Enums\InvoiceLogoType::BOTH)
                            @if ($setting['logo'] != '')
                                <img src="{{ asset('storage/' . $setting['logo']) }}" alt="Company Logo" class="h-16 mb-2">
                            @endif
                        @endif
                        @if (
                            $setting->invoice_logo_type == App\Enums\InvoiceLogoType::NAME ||
                                $setting->invoice_logo_type == App\Enums\InvoiceLogoType::BOTH)
                            <h2 class="font-bold mt-1 !text-black">{{ $setting['company_name'] }}</h2>
                        @endif
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
                            <span class="font-semibold">Invoice No: {{ $order->billno }}</span>
                        </div>
                        <div class="p-2 !text-black">
                            <span class="font-semibold">Date:
                                {{ Carbon\Carbon::parse($order->order_date)->format('d M, Y') }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 border-b border-gray-200">
                        <div class="p-2 border-gray-200 !text-black">
                            <span class="font-semibold">Client Name
                                :</span>{{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->customer_name }}
                        </div>
                        <div class="p-2"></div>
                    </div>
                    <div class="grid grid-cols-2 border-b border-gray-200">
                        <div class="p-2 border-gray-200 !text-black">
                            <span class="font-semibold">Address :</span>
                            {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->address }}
                        </div>
                        <div class="p-2"></div>
                    </div>
                    <div class="grid grid-cols-2">
                        <div class="p-2 border-gray-200 !text-black">
                            <span class="font-semibold">Mobile :</span>
                            {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->phone }}
                        </div>
                        <div class="p-2"></div>
                    </div>
                </div>

                <!-- Invoice table -->
                <div class="mb-6">
                    <div class="overflow-x-auto relative">

                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 !text-black">
                                    <th class="border border-gray-200 p-2 text-left w-12 !text-black">#</th>
                                    <th class="border border-gray-200 p-2 text-left !text-black">Details</th>
                                    <th class="border border-gray-200 p-2 text-center !text-black">Qty</th>
                                    <th class="border border-gray-200 p-2 text-right !text-black">Price</th>
                                    <th class="border border-gray-200 p-2 text-right !text-black">Net.A</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderitems as $key => $item)
                                    <tr>
                                        <td class="border border-gray-200 p-2 text-center !text-black">
                                            {{ $key + 1 }}
                                        </td>
                                        <td class="border border-gray-200 p-2 !text-black">
                                            {{ $item->product->product_name }}</td>
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
                                        Total :</td>
                                    <td class="whitespace-nowrap border border-gray-200 p-2 text-right !text-black">
                                        {{ number_format($order->total_no_discount, 2) }} Tk</td>
                                </tr>

                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td
                                        class="whitespace-nowrap border border-gray-200 p-2 text-right font-semibold !text-black">
                                        Discount :</td>
                                    <td class="whitespace-nowrap border border-gray-200 p-2 text-right !text-black">
                                        {{ is_numeric($order->discount) ? $order->discount . ' TK' : $order->discount ?? 0 . ' Tk' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td
                                        class="whitespace-nowrap border border-gray-200 p-2 text-right font-semibold !text-black">
                                        Grand Total :</td>
                                    <td class="whitespace-nowrap border border-gray-200 p-2 text-right !text-black">
                                        {{ $order->receivable }} Tk</td>
                                </tr>

                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td class="border border-gray-200 p-2 text-right font-semibold !text-black">Total
                                        Paid:
                                    </td>
                                    <td class="border border-gray-200 p-2 text-right !text-black">
                                        {{ number_format($order->paid, 2) }} Tk</td>
                                </tr>
                                @if ($previous_due > 0)
                                    <tr>
                                        <td colspan="3" class="border border-gray-200"></td>
                                        <td class="border border-gray-200 p-2 text-right font-semibold !text-black">
                                            Previous
                                            Due:
                                        </td>
                                        <td class="border border-gray-200 p-2 text-right !text-black">
                                            {{ number_format($previous_due, 2) }} Tk</td>
                                    </tr>
                                @endif
                                @if ($previous_due > 0)
                                    <tr>
                                        <td colspan="3" class="border border-gray-200"></td>
                                        <td class="border border-gray-200 p-2 text-right font-semibold !text-black">
                                            Current
                                            Due:
                                        </td>
                                        <td class="border border-gray-200 p-2 text-right !text-black">
                                            {{ number_format($order->due, 2) }} Tk</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td class="border border-gray-200 p-2 text-right font-semibold !text-black">Total
                                        Due:
                                    </td>
                                    <td class="border border-gray-200 p-2 text-right !text-black">
                                        {{ number_format($order->due + $previous_due, 2) }} Tk</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


                {{-- in word --}}
                <div class="mb-6 !text-black">
                    <p> <span class="font-semibold mb-1 "> In Word: </span>
                        {{ numberToBanglaWord($order->receivable) }}</p>
                    {{-- <div class="min-h-8"></div> --}}
                </div>
                <!-- Note -->
                <div class="mb-6 !text-black">
                    <p> <span class="font-semibold mb-1 "> Note: </span> {{ $order->note }}</p>
                    <div class="min-h-8"></div>
                </div>
            </div>

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
                    <a href="{{ route('filament.admin.pages.pos') }}"
                        class="bg-teal-500 text-white py-2 flex items-center justify-center gap-2 hover:bg-teal-600 !text-black">
                        <div style="display: flex">
                            <x-fas-reply class="w-5 h-5" />
                            <div style="margin-left: 5px">New Sale</div>
                        </div>
                    </a>
                    <a href="{{ route('filament.admin.resources.sales.index') }}"
                        class="bg-teal-500 text-white py-2 flex items-center justify-center gap-2 hover:bg-teal-600 !text-black">
                        <div style="display: flex">
                            <x-fas-reply class="w-5 h-5" />
                            <div style="margin-left: 5px">Sale List</div>
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
