<div>
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







    <div class="flex flex-col gap-y-6">
        <div class="fi-ta">
            <div
                class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">

                <div
                    class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10 !border-t-0">
                    <table
                        class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                        <thead class="divide-y divide-gray-200 dark:divide-white/5">

                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 "
                                    style=";">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Date
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 "
                                    style=";">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Sell Amount
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 "
                                    style=";">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Purchase Amount
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 "
                                    style=";">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Sell/Gross Profit
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6 "
                                    style=";">
                                    <span
                                        class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span
                                            class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Net Profit
                                        </span>
                                    </span>
                                </th>

                            </tr>

                        </thead>

                        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">


                            @forelse ($results as $data)
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
                                                                    {{ $data['date'] }}
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
                                                                    {{ number_format($data['sell_amount'], 2) }}
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
                                                                    {{ number_format($data['purchase_amount'], 2) }}
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
                                                                    {{ number_format($data['profit'], 2) }}
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
                                                                    {{ number_format($data['sell_amount'] - $data['purchase_amount'], 2) }}
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
                                        No data available

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
