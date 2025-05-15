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
        <table class="table-auto w-full border-collapse border border-gray-300">

            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 rounded-t-lg">
                    <th class="border border-gray-300 dark:border-gray-600 px-6 py-3 text-left rounded-tl-lg">Date</th>
                    <th class="border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">Particulars</th>
                    <th class="border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">Debit</th>
                    <th class="border border-gray-300 dark:border-gray-600 px-6 py-3 text-left">Credit</th>
                    <th class="border border-gray-300 dark:border-gray-600 px-6 py-3 text-left rounded-tr-lg">Balance
                    </th>
                </tr>
            </thead>
            <tbody>
                @php
                    $bal = 0;
                @endphp
                @forelse ($datas as $data)

                    @php
                         if ($data->type == 'order') {
                            $bal = $bal + abs($data->amount);
                        } elseif ($data->type == 'opening_balance') {
                            $bal = $data->amount;
                        } else {
                            $bal = $bal - abs($data->amount);
                        }

                    @endphp
                    <tr class="{{ $loop->odd ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-900' }}">
                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 dark:text-white">
                            {{ Carbon\Carbon::parse($data->date)->format('d M, Y') }}</td>
                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 dark:text-white">
                            @if ($data->type == 'history')
                                Received from Customer
                            @elseif($data->type == 'opening_balance')
                                {{ $data->particulars }}
                            @else
                                Sale #{{ $data->particulars }}
                            @endif
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 dark:text-white">
                            @if ($data->type == 'order' || $data->particulars == 'Opening Receivable')
                                {{ $data->amount }}

                            @endif
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 dark:text-white">
                            @if ($data->type == 'history' ||  $data->particulars == 'Opening Payable')
                                {{ $data->amount }}
                            @endif
                        </td>
                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-2 dark:text-white">

                            {{ number_format($bal ,2) }}


                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"
                            class="text-center border border-gray-300 dark:border-gray-600 px-4 py-2 dark:text-white">
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
