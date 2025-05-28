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





    <div class="mt-6">
        <div class="overflow-x-auto relative">
            <table class="table-auto w-full border-collapse border border-gray-300">
                <thead>
                    <tr>
                        <th colspan="3"
                            class="border border-gray-300 dark:border-gray-600 px-6 py-3 text-center bg-gray-100 dark:bg-gray-800 text-lg font-semibold text-gray-700 dark:text-gray-200">
                            Top Selling Products
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3"
                            class="border border-gray-300 dark:border-gray-600 px-6 py-3 text-center bg-gray-50 dark:bg-gray-700 text-sm text-gray-600 dark:text-gray-300">
                            Report From {{ \Carbon\Carbon::parse($this->start_date)->format('Y-m-d') }}
                            to
                            {{ \Carbon\Carbon::parse($this->end_date)->format('Y-m-d') }}

                        </th>
                    </tr>
                    <tr class="bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 rounded-t-lg">
                        <th
                            class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left rounded-tl-lg">
                            Name
                        </th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Code
                        </th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Sold Quantity
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datas as $data)
                        <tr class="{{ $loop->odd ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-900' }}">
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ $data->product_name }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ $data->product_code }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ getTotalStockInTextWithoutModal($data, $data->sold) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3"
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
