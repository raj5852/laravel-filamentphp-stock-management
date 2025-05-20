<div>
    <div class=" grid gap-4 grid-cols-1 md:grid-cols-2">

        <div>
            <div class="space-y-2">
                <div
                    class="p-2 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-900 dark:border-gray-700">
                    {{ $this->form }}

                    <div class="mt-6 bg-white dark:bg-gray-900 rounded-lg shadow">
                        <div class="overflow-x-auto relative">
                            <table
                                class="w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                <thead class="bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-100">
                                    <tr>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">Name</th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">Quantity</th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">Price</th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">Sub T</th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->products as $index => $product)
                                        <tr class="bg-white dark:bg-gray-900">
                                            <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                                {{ $product['name'] }}</td>

                                            <td
                                                class="{{ $product['unit_id'] !== null && $product['sub_unit'] !== null ? 'px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[300px]' : 'px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[200px]' }}">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="{{ $product['unit_id'] === null ? 'flex-1 flex items-center gap-2' : 'flex-1 flex flex-col md:flex-row md:gap-2 md:items-center' }}">
                                                        <label
                                                            class="text-sm text-gray-700 dark:text-gray-300">{{ $product['mainunit']['unit_name'] }}:</label>
                                                        <input type="text"
                                                            wire:model.live="products.{{ $index }}.main_unit_qty"
                                                            wire:change="updateMainQuantity({{ $index }}, $event.target.value)"
                                                            min="0"
                                                            class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0(?!$)/, '');" />
                                                    </div>
                                                    @if ($product['sub_unit'] !== null)
                                                        <div
                                                            class="flex-1 flex flex-col md:flex-row md:gap-2 md:items-center">
                                                            <label
                                                                class="text-sm text-gray-700 dark:text-gray-300">{{ $product['subunit']['unit_name'] }}:</label>
                                                            <input type="text"
                                                                wire:model.live="products.{{ $index }}.sub_unit_qty"
                                                                wire:change="updateSubQuantity({{ $index }}, $event.target.value)"
                                                                min="0"
                                                                class="w-full min-w-[70px] px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white"
                                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0(?!$)/, '');" />
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>

                                            <td
                                                class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[160px]">
                                                <div class="flex items-center gap-2 ">
                                                    <input type="text" min="0"
                                                        wire:model.live.debounce.10ms="products.{{ $index }}.rate"
                                                        class="w-full min-w-[70px] px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white"
                                                        oninput="
                                                            let value = this.value;
                                                            // Remove invalid characters
                                                            value = value.replace(/[^0-9.]/g, '');
                                                            // Ensure only one decimal point
                                                            value = value.replace(/(\..*?)\..*/g, '$1');
                                                            // Prevent leading zeros unless followed by a decimal point
                                                            value = value.replace(/^0(?![.])/g, '');
                                                            this.value = value;
                                                        " />
                                                </div>


                                            </td>



                                            <td
                                                class="px-4 py-2 border dark:border-gray-700 font-semibold text-center text-gray-900 dark:text-white">

                                                @php
                                                    $mainunitprice =
                                                        ($product['rate'] ?: 0) * ($product['main_unit_qty'] ?: 0);
                                                    if ($product['subunit'] != '') {
                                                        $SingleSubunitPrice =
                                                            ($product['rate'] ?: 0) / $product['related_by_value'];
                                                        $subunitPrice =
                                                            $SingleSubunitPrice * ($product['sub_unit_qty'] ?: 0);
                                                    } else {
                                                        $subunitPrice = 0;
                                                    }

                                                    echo number_format($mainunitprice + $subunitPrice, 2);
                                                @endphp
                                                Tk

                                            </td>
                                            <td class="px-4 py-2 border dark:border-gray-700 text-center">
                                                <button wire:click="removeProduct({{ $index }})"
                                                    class="text-gray-600 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400">
                                                    🗑️
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 dark:bg-gray-800">
                                        <td colspan="4"
                                            class="px-4 py-2 text-right font-bold border dark:border-gray-700 text-gray-800 dark:text-gray-200">
                                            Grand Total:
                                        </td>
                                        <td colspan="2"
                                            class="px-4 py-2 font-bold text-gray-900 dark:text-white border dark:border-gray-700">
                                            {{ number_format($this->grandTotal, 2) }} Tk
                                        </td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>

                        <div class="mt-4">
                            <x-filament::button wire:click="mountAction('payment')" :disabled="!count($this->products)">
                                <div style="display: flex">
                                    <x-fas-money-bill class="w-6 h-6 text-gray-500" style="color: white" />
                                    <div style="margin-left:5px">Payment</div>
                                </div>
                            </x-filament::button>
                        </div>
                    </div>
                    {{-- <div class="mt-6 bg-white dark:bg-gray-900 rounded-lg shadow ">
                        <div class="overflow-x-auto relative">
                            <table
                                class="  w-full text-sm text-left text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 ">
                                <thead class="bg-gray-200 dark:bg-gray-800 text-gray-800 dark:text-gray-100">
                                    <tr>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                            #SL
                                        </th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                            Product
                                        </th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                            Rate
                                        </th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                            Qty
                                        </th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                            Sub Total
                                        </th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700"></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr class="bg-white dark:bg-gray-900">
                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 text-center">
                                            1
                                        </td>
                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                            p2
                                        </td>

                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[160px]">
                                            <input type="number" min="0"
                                                wire:model.live.debounce.10ms="products.0.rate"
                                                class="w-full min-w-[70px] px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white" />
                                        </td>

                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[300px]">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 flex flex-col md:flex-row md:gap-2 md:items-center">
                                                    <label
                                                        class="text-sm text-gray-700 dark:text-gray-300">dozon:</label>
                                                    <input type="number" wire:model.live="products.0.main_unit_qty"
                                                        min="0"
                                                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0(?!$)/, '');" />
                                                </div>

                                                <div class="flex-1 flex flex-col md:flex-row md:gap-2 md:items-center">
                                                    <label class="text-sm text-gray-700 dark:text-gray-300">PC:</label>
                                                    <input type="number" wire:model.live="products.0.sub_unit_qty"
                                                        min="0"
                                                        class="w-full min-w-[70px] px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0(?!$)/, '');" />
                                                </div>

                                            </div>
                                        </td>

                                        <td
                                            class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 font-semibold text-center text-gray-900 dark:text-white">
                                            0.00 Tk
                                        </td>
                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 text-center">
                                            <button wire:click="removeProduct(0)"
                                                class="text-gray-600 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400">
                                                🗑️
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="bg-white dark:bg-gray-900">
                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 text-center">
                                            2
                                        </td>
                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                            Product
                                        </td>

                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[160px]">
                                            <div class="flex items-center gap-2 md:w-full">
                                                <input type="number" min="0"
                                                    wire:model.live.debounce.10ms="products.1.rate"
                                                    class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white" />
                                            </div>
                                        </td>

                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[200px]">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 flex items-center gap-2">
                                                    <label class="text-sm text-gray-700 dark:text-gray-300">PC:</label>
                                                    <input type="number" wire:model.live="products.1.main_unit_qty"
                                                        min="0"
                                                        class="w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0(?!$)/, '');" />
                                                </div>

                                            </div>
                                        </td>

                                        <td
                                            class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 font-semibold text-center text-gray-900 dark:text-white">
                                            0.00 Tk
                                        </td>
                                        <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 text-center">
                                            <button wire:click="removeProduct(1)"
                                                class="text-gray-600 hover:text-red-600 dark:text-gray-300 dark:hover:text-red-400">
                                                🗑️
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                                <tfoot>
                                    <tr class="bg-gray-100 dark:bg-gray-800">
                                        <td colspan="4"
                                            class="wrap px-4 py-2 text-right font-bold border dark:border-gray-700 text-gray-800 dark:text-gray-200">
                                            Grand Total:
                                        </td>
                                        <td
                                            class="text-center px-4 py-2 font-bold text-gray-900 dark:text-white border dark:border-gray-700">
                                            0.00 Tk
                                        </td>
                                        <td
                                            class="text-center px-4 py-2 font-bold text-gray-900 dark:text-white border dark:border-gray-700">
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div> --}}

                </div>
            </div>


        </div>

        <div class="">
            {{ $this->table }}
        </div>

        <x-filament-actions::modals />
    </div>
