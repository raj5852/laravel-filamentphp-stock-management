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
                top: 50px;
                width: 100%;
            }
            button, form, .print-hide {
                display: none !important;
            }
            .print-title{
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


    <br><br>


    <div class="flex flex-col">
        <div class="flex justify-between items-center">
            <div class="print-title hidden">
                <h2 class="text-2xl font-bold">Supplier Ledger</h2>
                @if(isset($supplier) && $supplier)
                <p class="text-lg">{{ $supplier->name }}</p>
                @endif
            </div>
            <div></div>
            <button onclick="window.print()" class="px-4 py-2 bg-primary-600 text-white rounded-lg shadow hover:bg-primary-700 transition-colors duration-200 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>
        <div class="fi-ta">
            <div
                class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">

                <div
                    class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 !border-t-0">
                    <table
                        class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                        <thead class="divide-y divide-gray-200 dark:divide-white/5">

                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Date
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Particulars
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Debit
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Credit
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
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
                                        $data->type == 'purchase' ||
                                        $data->particulars == 'Opening Payable'
                                    ) {
                                        $credit = $data->amount;
                                    } else {
                                        $credit = '';
                                    }

                                    // // Balance

                                    $bal = $bal + ($debit ?: 0) - ($credit ?: 0);

                                    // if ($data->type == 'purchase') {
                                    //     $bal = $bal - abs($data->amount);
                                    // } elseif ($data->type == 'opening_balance') {
                                    //     $bal = $data->amount;
                                    // } else {
                                    //     $bal = $bal + abs($data->amount);
                                    // }

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
                                                                    {{-- @if ($data->type == 'history')
                                                                        Paid to Supplier
                                                                    @elseif($data->type == 'opening_balance')
                                                                        {{ $data->particulars }}
                                                                    @else
                                                                        Purchase #{{ $data->particulars }}
                                                                    @endif --}}
                                                                    @if ($data->type == 'history')
                                                                        @if ($data->particulars == '2')
                                                                            Received from Supplier
                                                                        @else
                                                                            Paid to Supplier
                                                                        @endif
                                                                    @elseif($data->type == 'opening_balance')
                                                                        {{ $data->particulars }}
                                                                    @else
                                                                        Purchase #{{ $data->particulars }}
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
                                                                    {{-- @if ($data->type == 'history' || $data->particulars == 'Opening Receivable')
                                                                        {{ abs($data->amount) }}
                                                                    @endif --}}
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
                                                                    {{-- @if ($data->type == 'purchase' || $data->particulars == 'Opening Payable')
                                                                        {{ abs($data->amount) }}
                                                                    @endif --}}
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
                                        @if ($supplier_id)
                                            No data available
                                        @else
                                            Please Select Supplier
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
