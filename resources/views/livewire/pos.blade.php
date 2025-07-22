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
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">Size</th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">Quantity</th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">Price</th>
                                        <!-- <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">Discount %
                                        </th> -->
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 "
                                            style="width: 100px !important">
                                            Sub T</th>
                                        <th class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->products as $index => $product)
                                        <tr class="bg-white dark:bg-gray-900">
                                            <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                                {{ $product['name'] }}</td>
                                            <td class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700">
                                                @if($product['has_varient'])
                                                    <select wire:model.live="products.{{ $index }}.user_color_size" name="" id="" class="w-full min-w-[80px] px-1 sm:px-2 md:px-4 py-2 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-white" style="min-width: 100px;">
                                                        <option value="">Select Variation</option>
                                                        @foreach($product['color_size'] ?? [] as $item)
                                                            @if($item['status'] == 'true')
                                                            <option value="{{ $item['uniqid'] }}">
                                                                @if($item['color'] !== null)
                                                                 {{ $item['color'] }} - 
                                                                @endif
                                                                @if($item['size'] !== null)
                                                                 {{ $item['size'] }} - 
                                                                @endif
                                                                {{ $item['available_stock'] }}
                                                            </option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                @endif  
                                            </td>
                                            <td
                                                class="{{ $product['unit_id'] !== null && $product['sub_unit'] !== null ? 'px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[300px] ' : 'px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[200px] min-w-[50px] ' }}">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="relative {{ $product['unit_id'] === null ? 'flex-1 flex items-center gap-2 ' : 'flex-1 flex flex-col md:flex-row md:gap-2 md:items-center min-w-[70px]' }}">
                                                        <label
                                                            class="bg-white dark:bg-transparent  text-sm text-gray-700 dark:text-gray-300 absolute"
                                                            style="top: -10px; left: 5px">{{ $product['mainunit']['unit_name'] }}:</label>
                                                        <input type="text"
                                                            wire:model.live="products.{{ $index }}.main_unit_qty"
                                                            wire:change="updateMainQuantity({{ $index }}, $event.target.value)"
                                                            min="0"
                                                            class=" w-full px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0(?!$)/, '');" />
                                                    </div>
                                                    @if ($product['sub_unit'] !== null)
                                                        <div
                                                            class="relative flex-1 flex flex-col md:flex-row md:gap-2 md:items-center">
                                                            <label style="top: -10px; left: 5px"
                                                                class="bg-white dark:bg-transparent text-sm text-gray-700 dark:text-gray-300 absolute ">{{ $product['subunit']['unit_name'] }}:</label>
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
                                                            // Remove lone decimal point
                                                            value = value.replace(/^\./, '');
                                                            // Prevent leading zeros unless followed by a decimal point
                                                            value = value.replace(/^0(?![.])/g, '');
                                                            this.value = value;
                                                        " />
                                                </div>
                                            </td>

                                            <!-- <td
                                                class="px-1 sm:px-2 md:px-4 py-2 border dark:border-gray-700 md:w-[120px]">
                                                <div class="flex items-center gap-2">
                                                    <input type="text" min="0" max="100"
                                                        wire:model.live.debounce.10ms="products.{{ $index }}.discount_percentage"
                                                        class="w-full min-w-[70px] px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 text-center text-gray-900 dark:text-white"
                                                        oninput="
                                                            let value = this.value;
                                                            // Remove invalid characters
                                                            value = value.replace(/[^0-9.]/g, '');
                                                            // Ensure only one decimal point
                                                            value = value.replace(/(\..*?)\..*/g, '$1');
                                                            // Remove lone decimal point
                                                            value = value.replace(/^\./, '');
                                                            // Prevent leading zeros unless followed by a decimal point
                                                            value = value.replace(/^0(?![.])/g, '');
                                                            // Cap at 100
                                                            if (parseFloat(value) > 100) value = '100';
                                                            this.value = value;
                                                        " />
                                                </div>
                                            </td> -->

                                            <td
                                                class="px-4 py-2 border dark:border-gray-700 font-semibold text-center text-gray-900 dark:text-white min-w-[100px]">

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

                                                    $subtotal = $mainunitprice + $subunitPrice;
                                                    $discount =
                                                        ($subtotal * ($product['discount_percentage'] ?: 0)) / 100;
                                                    $final_subtotal = $subtotal - $discount;

                                                    echo number_format($final_subtotal, 2);
                                                @endphp
                                                Tk

                                            </td>
                                            <td class="px-4 py-2 border dark:border-gray-700 text-center">
                                                <button wire:click="removeProduct({{ $index }})"
                                                    class="flex items-center justify-center w-8 h-8 text-danger-600 hover:text-danger-700 hover:bg-danger-50 rounded-full transition duration-200">
                                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg"
                                                        viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
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


                </div>
            </div>


        </div>

        <div class="">
            {{ $this->table }}
        </div>

        <x-filament-actions::modals />
    </div>
