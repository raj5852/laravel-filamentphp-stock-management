<x-filament-panels::page>
    <!-- Invoice content -->
    <div class="max-w-4xl mx-auto p-1 md:p-6">
        <div class="bg-white shadow-md rounded-sm p-1 md:p-6">
            <!-- Company info -->
            <div class="flex flex-col md:flex-row justify-center md:justify-between mb-6">
                <div class="flex flex-col items-center">
                    <div class="bg-[#3498db] text-white px-3 py-1 mb-1">
                        <span class="font-bold">SOFT</span>
                        <span class="bg-white text-[#3498db] px-2 py-0.5 font-bold">GHOR</span>
                    </div>
                    <p class="text-xs text-gray-600 !text-black">Digital Solution Provider</p>
                    <h2 class="font-bold mt-1 !text-black">Softghor.Com</h2>
                </div>
                <div class="md:max-w-[250px] text-center md:text-left">
                    <p class="text-sm !text-black">
                        <span class="font-semibold">Address :</span> Holding: 53 (1st
                        floor), Road: 04 Block: G, Banasree, Dhaka 1219.
                    </p>
                    <p class="text-sm !text-black">
                        <span class="font-semibold">Phone :</span> 01779724380
                    </p>
                    <p class="text-sm !text-black">
                        <span class="font-semibold">Email :</span> info@softghor.com
                    </p>
                </div>
            </div>

            <!-- Invoice details -->
            <div class="border border-gray-200 mb-6">
                <div class="grid grid-cols-2 border-b border-gray-200">
                    <div class="p-2 border-gray-200 !text-black">
                        <span class="font-semibold">Invoice No: 17</span>
                    </div>
                    <div class="p-2 !text-black">
                        <span class="font-semibold">Date: 20 May, 2025</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 border-b border-gray-200">
                    <div class="p-2 border-gray-200 !text-black">
                        <span class="font-semibold">Supplier Name :</span> Default Supplier
                    </div>
                    <div class="p-2"></div>
                </div>
                <div class="grid grid-cols-2 border-b border-gray-200">
                    <div class="p-2 border-gray-200 !text-black">
                        <span class="font-semibold">Address :</span> Default Address
                    </div>
                    <div class="p-2"></div>
                </div>
                <div class="grid grid-cols-2">
                    <div class="p-2 border-gray-200 !text-black">
                        <span class="font-semibold">Mobile :</span> 111111
                    </div>
                    <div class="p-2"></div>
                </div>
            </div>

            <!-- Invoice table -->
            <div class="mb-6">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100 !text-black">
                            <th class="border border-gray-200 p-2 text-left w-12 !text-black">#</th>
                            <th class="border border-gray-200 p-2 text-left !text-black">Details</th>
                            <th class="border border-gray-200 p-2 text-center !text-black">Qty</th>
                            <th class="border border-gray-200 p-2 text-right !text-black">Price</th>
                            <th class="border border-gray-200 p-2 text-right !text-black">Net A</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-gray-200 p-2 text-center !text-black">1</td>
                            <td class="border border-gray-200 p-2 !text-black">napa extend | 00000016</td>
                            <td class="border border-gray-200 p-2 text-center !text-black">3 Dozen 2 pc</td>
                            <td class="border border-gray-200 p-2 text-right !text-black">1.00 Tk</td>
                            <td class="border border-gray-200 p-2 text-right !text-black">3.17 Tk</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="border border-gray-200"></td>
                            <td
                                class="whitespace-nowrap border border-gray-200 p-2 text-right font-semibold !text-black">
                                Grand Total :</td>
                            <td class="whitespace-nowrap border border-gray-200 p-2 text-right !text-black">3.17 Tk</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="border border-gray-200"></td>
                            <td class="border border-gray-200 p-2 text-right font-semibold !text-black">Paid :</td>
                            <td class="border border-gray-200 p-2 text-right !text-black">3.17 Tk</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="border border-gray-200"></td>
                            <td class="border border-gray-200 p-2 text-right font-semibold !text-black">Due :</td>
                            <td class="border border-gray-200 p-2 text-right !text-black">0.00 Tk</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Note -->
            <div class="mb-6">
                <p class="font-semibold mb-1 !text-black">Note:</p>
                <div class="min-h-8"></div>
            </div>

            <!-- Action buttons -->
            <div class="mb-6">
                <button
                    class="w-full bg-gray-200 text-gray-800 py-2 flex items-center justify-center gap-2 hover:bg-gray-300 !text-black">
                    <span>Print</span>
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <button
                    class="bg-teal-500 text-white py-2 flex items-center justify-center gap-2 hover:bg-teal-600 !text-black">
                    <span>New Purchase</span>
                </button>
                <button
                    class="bg-teal-500 text-white py-2 flex items-center justify-center gap-2 hover:bg-teal-600 !text-black">
                    <span>Purchase List</span>
                </button>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-600 !text-black">
            <p>
                Copyright © 2025 <span class="text-teal-500 !text-black">SOFTGHOR</span>. All
                rights reserved.
            </p>
        </div>
    </div>
</x-filament-panels::page>
