<div>
    {{-- Because she competes with no one, no one can compete with her. --}}
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

    <div class="mt-6">
        <div class="overflow-x-auto relative">

            <table class=" w-full border-collapse border border-gray-300">

                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 rounded-t-lg">
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left ">
                            Month</th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Sales</th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Cost of Goods Sold
                        </th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Gross Profit
                        </th>
                        <th
                            class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left rounded-tr-lg">
                            Net Profit
                        </th>
                    </tr>
                </thead>
                <tbody>

                    @forelse ($orderitems as $data)
                        <tr class="{{ $loop->odd ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-900' }}">

                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ \Carbon\Carbon::createFromDate($data->year, $data->month, 1)->format('M Y') }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ number_format($data->sales, 2) }}

                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ number_format($data->cost_of_goods_sold, 2) }}

                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ number_format($data->gross_profit, 2) }}

                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ number_format($data->gross_profit, 2) }}

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="whitespace-nowrap text-center border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                No data available

                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>
