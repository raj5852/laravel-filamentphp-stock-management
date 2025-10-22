<x-filament-panels::page>


    @php
        $balance = 0;

        if ($customer->wallet <= 0) {
            $balance = abs($customer->wallet);
        } else {
            $balance = 0;
        }

        $all_return_due = -App\Models\Order::query()
            ->where('customer_id', $order->customer_id)
            ->where('paid', '>', 0)
            ->whereHas('returnlist')
            ->sum('paid');

        $previous_due = (abs($balance) + $customer->orders_sum_due ?: 0) - $order->due;

    @endphp

    @if (2 == 2)
        <div
            style="font-family: 'Arial', sans-serif; font-size: 12px; width: 80mm; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 6px; color: #000;">
            <div id="receipt-container">

                <!-- Header with Logo -->
                <div
                    style="text-align: center; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dotted black;">
                    @if (
                        $setting->invoice_logo_type == App\Enums\InvoiceLogoType::LOGO ||
                            $setting->invoice_logo_type == App\Enums\InvoiceLogoType::BOTH)
                        @if ($setting['logo'] != '')
                            <img src="{{ asset('storage/' . $setting['logo']) }}" alt="Logo"
                                style="max-width: 60px; height: auto; margin: 0 auto; display: block; margin-bottom: 5px;" />
                        @endif

                    @endif

                    @if (
                        $setting->invoice_logo_type == App\Enums\InvoiceLogoType::NAME ||
                            $setting->invoice_logo_type == App\Enums\InvoiceLogoType::BOTH)
                        <h2 style="margin: 5px 0; font-size: 16px; color: #000000; font-weight: 600;">
                            {{ $setting['company_name'] }}
                        </h2>
                    @endif

                </div>

                <!-- Business Info -->
                <div
                    style="text-align: center; margin-bottom: 10px; padding: 6px; border-radius: 6px; border: 1px dotted black; background-color: #ffffff;">
                    <p style="margin: 2px 0; font-size: 11px;"><strong>Address:</strong> {{ $setting['address'] }}</p>
                    <p style="margin: 2px 0; font-size: 11px;"><strong>Phone:</strong> {{ $setting['phone'] }}</p>
                    <p style="margin: 2px 0; font-size: 11px;"><strong>Email:</strong> {{ $setting['email_address'] }}
                    </p>
                </div>

                <!-- Invoice Details -->
                <div
                    style="display: flex; justify-content: space-between; padding: 8px; margin-bottom: 10px; border-radius: 6px; border: 1px dotted black; background-color: #ffffff;">
                    <div style="width: 48%;">
                        <p style="margin: 2px 0; font-size: 11px;"><strong>Invoice No:</strong>
                            {{ $order->invoiceno }}
                        </p>
                        <p style="margin: 2px 0; font-size: 11px;"><strong>Client Name:</strong>
                            {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->customer_name }}</p>
                        <p style="margin: 2px 0; font-size: 11px;"><strong>Mobile:</strong>
                            {{ $customer->is_default == 1 ? 'Walk-in Customer' : $customer->phone }}</p>
                    </div>
                    <div style="width: 48%;">
                        <p style="margin: 2px 0; font-size: 11px;"><strong>Date:</strong>
                            {{ Carbon\Carbon::parse($order->order_date)->format('d M, Y') }}</p>
                        {{-- <p style="margin: 2px 0; font-size: 11px;"><strong>Payment:</strong> Cash</p> --}}
                    </div>
                </div>

                <!-- Table -->
                <div
                    style="border-radius: 6px; margin-bottom: 10px; overflow: hidden; border: 1px dotted black; background-color: #ffffff;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                        <thead>
                            <tr>
                                <th style="padding: 5px; text-align: left; border-bottom: 1px dotted black;">#</th>
                                <th style="padding: 5px; text-align: left; border-bottom: 1px dotted black;">Details
                                </th>
                                <th style="padding: 5px; text-align: center; border-bottom: 1px dotted black;">Qty</th>
                                <th style="padding: 5px; text-align: right; border-bottom: 1px dotted black;">Price
                                </th>
                                <th style="padding: 5px; text-align: right; border-bottom: 1px dotted black;">Net A
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->orderitems as $key => $item)
                                <tr>

                                    <td style="padding: 4px; text-align: center;">{{ $key + 1 }}</td>
                                    <td style="padding: 4px;">{{ $item->product->product_name }}</td>

                                    <td style="padding: 4px; text-align: center;">{{ $item->total_in_text }}</td>

                                    <td style="padding: 4px; text-align: center;">{{ number_format($item->rate, 2) }}
                                    </td>
                                    <td style="padding: 4px; text-align: center;">
                                        {{ number_format($item->total_rate, 2) }} </td>

                                </tr>
                            @endforeach




                            <!-- Summary Section -->
                            <tr>
                                <td colspan="4"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong>Total:</strong>
                                </td>
                                <td colspan="2"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong>{{ number_format($order->total_no_discount + $order->returnlist->total_no_discount, 2) }}
                                    </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong>Discount:</strong>
                                </td>
                                <td colspan="2"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong>{{ is_numeric($order->discount) ? $order->discount . ' TK' : $order->discount ?? 0 . ' Tk' }}</strong>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="4"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong>Grand Total:</strong>
                                </td>
                                <td colspan="2"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong> {{ $order->receivable + $order->returnlist->receivable }} Tk</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong>Total
                                        Paid:</strong>
                                </td>
                                <td colspan="2"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong> {{ number_format($order->paid, 2) }}</strong>
                                </td>
                            </tr>
                            @if ($previous_due > 0)
                                <tr>
                                    <td colspan="4"
                                        style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                        <strong>Previous
                                            Due:</strong>
                                    </td>
                                    <td colspan="2"
                                        style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                        <strong> {{ number_format($previous_due, 2) }} </strong>
                                    </td>
                                </tr>
                            @endif
                            @if ($previous_due > 0)
                                <tr>
                                    <td colspan="4"
                                        style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                        <strong>Current
                                            Due:</strong>
                                    </td>
                                    <td colspan="2"
                                        style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                        <strong> {{ number_format($order->due, 2) }} </strong>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong> Total
                                        Due:</strong>
                                </td>
                                <td colspan="2"
                                    style="border-top: 1px dotted black; padding: 4px; text-align: right; background-color: #ffffff;">
                                    <strong>
                                        {{ number_format($order->due - $order->returnlist->paid + $previous_due, 2) }}</strong>
                                </td>
                            </tr>


                        </tbody>
                    </table>
                </div>

                <!-- Amount in words -->
                <div
                    style="margin-bottom: 10px; padding: 6px; border-radius: 6px; font-size: 11px; border: 1px dotted black; background-color: #ffffff;">
                    <p style="margin: 0;"><strong>In Words:</strong> {{ numberToBanglaWord($order->receivable) }} Taka
                        Only</p>
                </div>

                <div
                    style="margin-bottom: 10px; padding: 6px; border-radius: 6px; font-size: 11px; border: 1px dotted black; background-color: #ffffff;">
                    <p style="margin: 0;"><strong>Note :</strong> {{ $order->note }} </p>
                </div>


                <div
                    style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dotted black; padding-top: 8px;">
                    <!-- QR Code -->
                    <div>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data={{ $order->invoiceno }}"
                            alt="QR Code" style="border-radius: 4px; border: 1px dotted black;" />
                    </div>

                    <!-- Thank You -->
                    <div style="text-align: right; width: 60%;">
                        <p style="margin: 0; color: #000000; font-weight: 600;">Thank you for your Order!</p>
                        <p style="margin: 0; font-size: 10px; color: #333333;">Please visit again.</p>
                    </div>
                </div>
            </div>


        </div>

        <!-- Action Buttons -->
        <div style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
            <button onclick="printInvoice()" class="pos-button print-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    viewBox="0 0 16 16">
                    <path
                        d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z" />
                    <path
                        d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                </svg>
                Print
            </button>
            <a href="{{ route('filament.admin.resources.sales.index') }}" class="pos-button list-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    viewBox="0 0 16 16">
                    <path
                        d="M1.5 1.5A.5.5 0 0 1 2 1h12a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.128.334L10 8.692V13.5a.5.5 0 0 1-.342.474l-3 1A.5.5 0 0 1 6 14.5V8.692L1.628 3.834A.5.5 0 0 1 1.5 3.5v-2z" />
                </svg>
                Sale List
            </a>
            <a href="{{ route('filament.admin.pages.pos') }}" class="pos-button new-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    viewBox="0 0 16 16">
                    <path
                        d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                </svg>
                New Sale
            </a>
        </div>

        <style>
            /* Base button styles */
            .pos-button {
                background-color: #000000;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 5px;
                text-decoration: none;
            }

            /* Specific button styles */
            .list-button {
                background-color: #333333;
            }

            /* Dark mode styles */
            @media (prefers-color-scheme: dark) {
                .pos-button {
                    background-color: #ffffff !important;
                    color: #000000 !important;
                    border: 1px solid #333333 !important;
                    transition: all 0.3s ease;
                }

                .pos-button:hover {
                    background-color: #f0f0f0 !important;
                    box-shadow: 0 0 8px rgba(255, 255, 255, 0.5);
                }

                .pos-button svg {
                    fill: #000000 !important;
                }
            }
        </style>
        <script>
            function printInvoice() {
                // Create a print-specific stylesheet
                const style = document.createElement('style');
                style.innerHTML = `
                @media print {
                    @page {
                        size: 80mm auto;  /* Width 80mm, height auto */
                        margin: 2mm;
                    }
                    body * {
                        visibility: hidden;
                    }
                    #receipt-container, #receipt-container * {
                        visibility: visible;
                    }
                    #receipt-container {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                        margin: 0;
                        padding: 0;
                    }
                }
            `;
                document.head.appendChild(style);

                // Trigger the print dialog
                window.print();

                // Remove the style after printing
                setTimeout(() => {
                    document.head.removeChild(style);
                }, 1000);
            }
        </script>
    @endif

</x-filament-panels::page>
