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

                    <tr class="bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 rounded-t-lg">
                        <th
                            class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left rounded-tl-lg">
                            Date
                        </th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Sell Amount
                        </th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Purchase Amount
                        </th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Sell/Gross Profit
                        </th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Net Profit
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($results as $data)
                        <tr class="{{ $loop->odd ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-900' }}">
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ $data['date'] }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ number_format($data['sell_amount'], 2) }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ number_format($data['purchase_amount'], 2) }}

                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ number_format($data['profit'], 2) }}

                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ number_format($data['sell_amount'] - $data['purchase_amount'], 2) }}

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
