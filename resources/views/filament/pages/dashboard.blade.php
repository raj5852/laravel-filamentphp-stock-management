<x-filament-panels::page>
    <div class="overflow-x-auto relative text-black dark:bg-gray-900 dark:text-white rounded-xl">
        <table class="table-auto w-full border-collapse divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
            <thead>
                <tr class="text-left text-sm rounded-t-lg bg-gray-50 dark:bg-white/5">
                    <th class="px-4 py-5">Date</th>
                    <th class="px-4 py-5">Sell Amount</th>
                    <th class="px-4 py-5">Purchase Amount</th>
                    <th class="px-4 py-5">Sell/Gross Profit</th>
                    <th class="px-4 py-5">Net Profit</th>
                </tr>
            </thead>
            <tbody>
                <script>
                    for (let i = 0; i < 10; i++) {
                        document.write(`
                        <tr class="bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-white/5 border-b border-gray-200 dark:border-white/5 last:border-b-0">
                            <td class="px-4 py-4">2323</td>
                            <td class="px-4 py-3">32</td>
                            <td class="px-4 py-3">323</td>
                            <td class="px-4 py-3">323</td>
                            <td class="px-4 py-3">21212</td>
                        </tr>
                    `);
                    }
                </script>
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
