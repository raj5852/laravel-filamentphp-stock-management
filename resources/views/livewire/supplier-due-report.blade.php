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
                            Name</th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Email</th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Phone
                        </th>
                        <th class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">
                            Address
                        </th>
                        <th
                            class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left rounded-tr-lg">
                            Invoice Due
                        </th>
                        <th
                            class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left rounded-tr-lg">
                            Direct Due
                        </th>
                        <th
                            class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-6 py-3 text-left rounded-tr-lg">
                            Total Due
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datas as $data)
                        <tr class="{{ $loop->odd ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-900' }}">


                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ $data->supplier_name }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ $data->email }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ $data->phone }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                {{ $data->address }}
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                <span class="font-bold"> {{ number_format($data->purchases_sum_due ?? 0, 2) }} TK</span>
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                <span class="font-bold"> {{ number_format(abs($data->wallet), 2) }} TK</span>
                            </td>
                            <td
                                class="whitespace-nowrap border border-gray-300 dark:border-gray-600 px-4 py-3 dark:text-white">
                                <span class="font-bold"> {{ number_format(abs($data->total_dues), 2) }} TK </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
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
