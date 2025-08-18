<div>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .fi-ta, .fi-ta *, .print-title, .print-title * {
                visibility: visible;
            }
            .print-title {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
            .fi-ta {
                position: absolute;
                left: 0;
                top: 100px;
                width: 100%;
            }
            button, form, .print-hide {
                display: none !important;
            }
        }
    </style>
    <form wire:submit.prevent="submit">
        {{ $this->form }}

        <x-filament::button wire:click="filter" class="mt-4">

            <div style="display: flex">
                <x-fas-sliders class="w-5 h-5" />
                <div style="margin-left: 5px">Filter</div>
            </div>
        </x-filament::button>
    </form>

    <div class="print-title" style="display: none;">
        <h1 style="font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 10px;">Customer Ledger</h1>
        @if(isset($customer_name))
        <h2 style="font-size: 18px; text-align: center; margin-bottom: 20px;">{{ $customer_name }}</h2>
        @endif
    </div>
    
    <div class="flex justify-end mb-2">
        <button onclick="window.print()" class="inline-flex items-center justify-center py-2 px-4 text-sm font-medium tracking-tight rounded-lg text-white bg-primary-600 hover:bg-primary-500 focus:bg-primary-700 focus:outline-none focus:ring-offset-0 focus:ring-2 focus:ring-primary-600 print-hide">
            <svg class="w-5 h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Print
        </button>
    </div>

    <div class="flex flex-col gap-y-2">
        <div class="fi-ta">
            <div
                class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">

                <div
                    class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 !border-t-0">
                    <table
                        class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                        <thead class="divide-y divide-gray-200 dark:divide-white/5">

                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 ">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Date
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 ">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Particulars
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 ">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Debit
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 ">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Credit
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 ">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Balance
                                        </span>
                                    </span>
                                </th>


                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">

                            @php
                                $bal = 0;
                            @endphp

                            @forelse ($datas as $data)
                                @php
                                    // Debit
                                    if (
                                        $data->type == 'order' ||
                                        $data->particulars == 'Opening Receivable' ||
                                        $data->particulars == '3'
                                    ) {
                                        $debit = $data->amount;
                                    } else {
                                        $debit = '';
                                    }

                                    // Credit

                                    if (
                                        ($data->type == 'history' && $data->particulars == '2') ||
                                        $data->particulars == 'Opening Payable'
                                    ) {
                                        $credit = $data->amount;
                                    } else {
                                        $credit = '';
                                    }

                                    // Balance

                                    $bal = $bal + ($debit ?: 0) - ($credit ?: 0);
                                @endphp

                                <tr
                                    class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75">

                                    <td
                                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 ">
                                        <div class="fi-ta-col-wrp">
                                            <div
                                                class="flex w-full disabled:pointer-events-none justify-start text-start">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">


                                                    <div class="flex ">
                                                        <div class="flex max-w-max" style="">
                                                            <div
                                                                class="fi-ta-text-item inline-flex items-center gap-1.5  ">

                                                                <span
                                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                                    style="">
                                                                    {{ Carbon\Carbon::parse($data->date)->format('d M, Y') }}
                                                                </span>

                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 ">
                                        <div class="fi-ta-col-wrp">
                                            <div
                                                class="flex w-full disabled:pointer-events-none justify-start text-start">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">


                                                    <div class="flex ">
                                                        <div class="flex max-w-max" style="">
                                                            <div
                                                                class="fi-ta-text-item inline-flex items-center gap-1.5  ">

                                                                <span
                                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                                    style="">
                                                                    @if ($data->type == 'history')
                                                                        @if ($data->particulars == '2')
                                                                            Received from Customer
                                                                        @else
                                                                            Paid to Customer
                                                                        @endif
                                                                    @elseif($data->type == 'opening_balance')
                                                                        {{ $data->particulars }}
                                                                    @else
                                                                        Sale #{{ $data->particulars }}
                                                                    @endif
                                                                </span>

                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </td>

                                    <td
                                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 ">
                                        <div class="fi-ta-col-wrp">
                                            <div
                                                class="flex w-full disabled:pointer-events-none justify-start text-start">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">


                                                    <div class="flex ">
                                                        <div class="flex max-w-max" style="">
                                                            <div
                                                                class="fi-ta-text-item inline-flex items-center gap-1.5  ">

                                                                <span
                                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                                    style="">
                                                                    {{ $debit }}
                                                                </span>

                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </td>
                                    <td
                                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 ">
                                        <div class="fi-ta-col-wrp">
                                            <div
                                                class="flex w-full disabled:pointer-events-none justify-start text-start">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">


                                                    <div class="flex ">
                                                        <div class="flex max-w-max" style="">
                                                            <div
                                                                class="fi-ta-text-item inline-flex items-center gap-1.5  ">

                                                                <span
                                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                                    style="">
                                                                    {{ $credit }}
                                                                </span>

                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </td>

                                    <td
                                        class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3 ">
                                        <div class="fi-ta-col-wrp">
                                            <div
                                                class="flex w-full disabled:pointer-events-none justify-start text-start">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">


                                                    <div class="flex ">
                                                        <div class="flex max-w-max" style="">
                                                            <div
                                                                class="fi-ta-text-item inline-flex items-center gap-1.5  ">

                                                                <span
                                                                    class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white  "
                                                                    style="">
                                                                    {{ number_format($bal, 2) }}
                                                                </span>

                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </td>



                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="whitespace-nowrap text-center dark:border-gray-600 px-4 py-3 dark:text-white">
                                        @if ($customer_id)
                                            No data available
                                        @else
                                            Please Select Customer
                                        @endif

                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>
                </div>
            </div>

        </div>
    </div>

</div>
