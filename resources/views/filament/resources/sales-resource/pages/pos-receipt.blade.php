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

    <!-- A4 size container with proper margins -->
    <div class="a4-container mx-auto p-0">
        <div class="bg-white shadow-md rounded-sm p-4 md:p-6 a4-content">
            <div id="invoice-container">

                <!-- Company info with improved layout -->
                <div class="flex flex-col md:flex-row justify-between items-center mb-6">
                    <div class="flex flex-col items-center md:items-start">
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
                            <h2 class="font-bold mt-1 !text-black text-xl">{{ $setting['company_name'] }}</h2>
                        @endif
                    </div>
                    <div class="md:max-w-[300px] text-center md:text-right mt-3 md:mt-0">
                        <p class="text-sm !text-black">
                            <span class="font-semibold">Address:</span> {{ $setting['address'] }}
                        </p>
                        <p class="text-sm !text-black">
                            <span class="font-semibold">Phone:</span> {{ $setting['phone'] }}
                        </p>
                        <p class="text-sm !text-black">
                            <span class="font-semibold">Email:</span> {{ $setting['email_address'] }}
                        </p>
                    </div>
                </div>

                <!-- Invoice details with better spacing -->
                <div class="border border-gray-200 mb-6">
                    <div class="grid grid-cols-2 border-b border-gray-200">
                        <div class="p-1 border-gray-200 !text-black">
                            <span class="font-semibold">Invoice No: {{ $order->invoiceno }}</span>
                        </div>
                        <div class="p-1 !text-black text-right">
                            <span class="font-semibold">Date:
                                {{ Carbon\Carbon::parse($order->order_date)->format('d M, Y') }}</span>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 border-b border-gray-200">
                        <div class="p-1 border-gray-200 !text-black">
                            <span class="font-semibold">Client Name:
                            </span>{{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->customer_name }}
                        </div>
                        <div class="p-1"></div>
                    </div>
                    {{-- <div class="grid grid-cols-2 border-b border-gray-200">
                        <div class="p-1 border-gray-200 !text-black">
                            <span class="font-semibold">Address:</span>
                            {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->address }}
                        </div>
                        <div class="p-1"></div>
                    </div> --}}
                    <div class="grid grid-cols-2">
                        <div class="p-1 border-gray-200 !text-black">
                            <span class="font-semibold">Mobile:</span>
                            {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->phone }}
                        </div>
                        <div class="p-1"></div>
                    </div>
                </div>

                <!-- Invoice table with optimized column widths and reduced row height -->
                <div class="mb-6">
                    <div class="overflow-x-auto relative">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-100 !text-black">
                                    <th class="border border-gray-200 py-1 px-2 text-left w-10 !text-black">#</th>
                                    <th class="border border-gray-200 py-1 px-2 text-left !text-black w-[45%]">Details
                                    </th>
                                    <th class="border border-gray-200 py-1 px-2 text-right !text-black w-[15%]">Rack
                                    </th>
                                    <th class="border border-gray-200 py-1 px-2 text-right !text-black w-[15%]">Price
                                    </th>
                                    <th class="border border-gray-200 py-1 px-2 text-center !text-black w-[15%]">Qty
                                    </th>
                                    <th class="border border-gray-200 py-1 px-2 text-right !text-black w-[15%]">Net.A
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->orderitems as $key => $item)
                                    <tr>
                                        <td class="border border-gray-200 py-0.5 px-2 text-center !text-black">
                                            {{ $key + 1 }}
                                        </td>
                                        <td class="border border-gray-200 py-0.5 px-2 !text-black">
                                            {{ $item->product->product_name }}</td>
                                        <td class="border border-gray-200 py-0.5 px-2 text-right !text-black">
                                            {{ $item->product?->rack?->rack_name }} </td>
                                        <td class="border border-gray-200 py-0.5 px-2 text-right !text-black">
                                            {{ number_format($item->rate, 2) }} Tk</td>
                                        <td class="border border-gray-200 py-0.5 px-2 text-center !text-black">
                                            {{ $item->total_in_text }}</td>

                                        <td class="border border-gray-200 py-0.5 px-2 text-right !text-black">
                                            {{ number_format($item->total_rate, 2) }} Tk</td>
                                    </tr>
                                @endforeach

                                <!-- Summary rows with consistent formatting -->
                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td
                                        class="whitespace-nowrap border border-gray-200 py-0.5 px-2 text-right font-semibold !text-black">
                                        Total:</td>
                                    <td
                                        class="whitespace-nowrap border border-gray-200 py-0.5 px-2 text-right !text-black">
                                        {{ number_format($order->total_no_discount, 2) }} Tk</td>
                                </tr>

                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td
                                        class="whitespace-nowrap border border-gray-200 py-0.5 px-2 text-right font-semibold !text-black">
                                        Discount:</td>
                                    <td
                                        class="whitespace-nowrap border border-gray-200 py-0.5 px-2 text-right !text-black">
                                        {{ is_numeric($order->discount) ? $order->discount . ' TK' : $order->discount ?? 0 . ' Tk' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td
                                        class="whitespace-nowrap border border-gray-200 py-0.5 px-2 text-right font-semibold !text-black">
                                        Grand Total:</td>
                                    <td
                                        class="whitespace-nowrap border border-gray-200 py-0.5 px-2 text-right !text-black">
                                        {{ $order->receivable }} Tk</td>
                                </tr>

                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td class="border border-gray-200 py-0.5 px-2 text-right font-semibold !text-black">
                                        Total
                                        Paid:
                                    </td>
                                    <td class="border border-gray-200 py-0.5 px-2 text-right !text-black">
                                        {{ number_format($order->paid, 2) }} Tk</td>
                                </tr>
                                @if ($previous_due > 0)
                                    <tr>
                                        <td colspan="3" class="border border-gray-200"></td>
                                        <td
                                            class="border border-gray-200 py-0.5 px-2 text-right font-semibold !text-black">
                                            Previous
                                            Due:
                                        </td>
                                        <td class="border border-gray-200 py-0.5 px-2 text-right !text-black">
                                            {{ number_format($previous_due, 2) }} Tk</td>
                                    </tr>
                                @endif
                                @if ($previous_due > 0)
                                    <tr>
                                        <td colspan="3" class="border border-gray-200"></td>
                                        <td
                                            class="border border-gray-200 py-0.5 px-2 text-right font-semibold !text-black">
                                            Current
                                            Due:
                                        </td>
                                        <td class="border border-gray-200 py-0.5 px-2 text-right !text-black">
                                            {{ number_format($order->due, 2) }} Tk</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="border border-gray-200"></td>
                                    <td class="border border-gray-200 py-0.5 px-2 text-right font-semibold !text-black">
                                        Total
                                        Due:
                                    </td>
                                    <td class="border border-gray-200 py-0.5 px-2 text-right !text-black">
                                        {{ number_format($order->due + $previous_due, 2) }} Tk</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- In word section -->
                <div class="mb-4 !text-black">
                    <p><span class="font-semibold">In Word: </span>
                        {{ numberToBanglaWord($order->receivable) }} Taka Only</p>
                </div>

                <!-- Note section -->
                <div class="mb-4 !text-black">
                    <p><span class="font-semibold">Note: </span> {{ $order->note }}</p>
                </div>

                <!-- Signature section -->
                {{-- <div class="mt-8 pt-4 flex justify-between">
                    <div class="text-center">
                        <div class="border-t border-gray-400 pt-1 w-32"></div>
                        <p class="text-sm !text-black">Customer Signature</p>
                    </div>
                    <div class="text-center">
                        <div class="border-t border-gray-400 pt-1 w-32"></div>
                        <p class="text-sm !text-black">Authorized Signature</p>
                    </div>
                </div> --}}

                <!-- Footer text -->
                <div class="mt-6 text-center text-xs text-gray-500">
                    <!-- <p>Thank you for your business!</p> -->
                </div>
            </div>

            <!-- Action buttons -->
            <div class="hiddenButtons">
                <div class="mb-6">
                    <button onclick="printInvoice()"
                        class="w-full bg-gray-200 text-gray-800 py-2 flex items-center justify-center gap-2 hover:bg-gray-300 !text-black">
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

    <style>
        /* A4 size styling */
        .a4-container {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            margin: 0 auto;
        }

        .a4-content {
            /* padding: 15mm 10mm; */
        }

        /* Compact table styling */
        table.border-collapse {
            line-height: 1.4;
        }

        /* Button styling */
        .hiddenButtons button,
        .hiddenButtons a {
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .hiddenButtons button:hover,
        .hiddenButtons a:hover {
            transform: translateY(-1px);
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .a4-container {
                width: 100%;
                height: auto;
                box-shadow: none;
            }

            .a4-content {
                padding: 0;
            }

            @page {
                size: A4;
                margin: 10mm;
            }

            .hiddenButtons {
                display: none !important;
            }
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .a4-container {
                width: 100%;
                min-height: auto;
            }

            .a4-content {
                padding: 10mm 5mm;
            }

            table {
                font-size: 0.9rem;
            }

            th,
            td {
                padding: 0.25rem !important;
            }

            .hiddenButtons button,
            .hiddenButtons a {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            table {
                font-size: 0.8rem;
            }

            .a4-content {
                padding: 5mm 2mm;
            }

            .hiddenButtons .grid {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
        }
    </style>

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
