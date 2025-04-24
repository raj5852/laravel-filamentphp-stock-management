<x-filament-panels::page>
    <div class="max-w-4xl mx-auto p-4 sm:p-6 bg-white text-slate-500 shadow-md rounded border font-sans">
        <!-- Header -->
        <div class="border-b pb-6 sm:pb-8 mb-6 sm:mb-8 flex flex-col sm:flex-row items-start sm:items-center">
            <div class="flex-1 text-left">
                <img src="https://themelize.me/wp-content/uploads/2024/10/cropped-logo-inline.webp" alt="Company Logo"
                    class="h-10 w-auto mb-4 sm:mb-0">
                <p class="text-sm text-slate-500">123 Business Street, City, Country</p>
                <p class="text-sm text-slate-500">Phone: (123) 456-7890</p>
                <p class="text-sm text-slate-500">Email: contact@company.com</p>
            </div>
            <h1 class="text-3xl sm:text-4xl font-bold text-slate-400 uppercase text-right mt-4 sm:mt-0">Invoice</h1>
        </div>

        <!-- Customer and Invoice Info -->
        <div class="flex flex-col sm:flex-row items-start sm:items-stretch pb-4 sm:pb-6 mb-4 sm:mb-6 gap-4">
            <!-- Customer Information -->
            <div class="flex-1">
                <h2 class="font-semibold text-slate-700">Bill To:</h2>
                <p class="text-slate-600">Client Name</p>
                <p class="text-sm text-slate-500">Client Address</p>
                <p class="text-sm text-slate-500">Client City, Country</p>
                <p class="text-sm text-slate-500">Phone: (987) 654-3210</p>
                <p class="text-sm text-slate-500">Email: client@example.com</p>
            </div>

            <!-- Invoice Information -->
            <div class="flex-1 text-left sm:text-right">
                <p class="text-sm text-slate-500">Invoice #: 12345</p>
                <p class="text-sm text-slate-500">Date: 2024-10-10</p>
                <p class="text-sm text-slate-500">Due Date: 2024-11-10</p>
            </div>
        </div>

        <!-- Invoice Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="px-4 py-2 text-left text-sm">Item</th>
                        <th class="px-4 py-2 text-left text-sm">Description</th>
                        <th class="px-4 py-2 text-left text-sm">Qty</th>
                        <th class="px-4 py-2 text-left text-sm">Price</th>
                        <th class="px-4 py-2 text-right text-sm">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border px-4 py-2">Service A</td>
                        <td class="border px-4 py-2">Web Design Service</td>
                        <td class="border px-4 py-2">1</td>
                        <td class="border px-4 py-2">$100.00</td>
                        <td class="border px-4 py-2 text-right">$100.00</td>
                    </tr>
                    <tr>
                        <td class="border px-4 py-2">Service B</td>
                        <td class="border px-4 py-2">SEO Optimization</td>
                        <td class="border px-4 py-2">2</td>
                        <td class="border px-4 py-2">$50.00</td>
                        <td class="border px-4 py-2 text-right">$100.00</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="border px-4 py-2 text-right">Subtotal</td>
                        <td class="border px-4 py-2 text-right">$200.00</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="border px-4 py-2 text-right">Tax (10%)</td>
                        <td class="border px-4 py-2 text-right">$20.00</td>
                    </tr>
                    <tr class="font-bold">
                        <td colspan="4" class="border px-4 py-2 text-right">Total Due</td>
                        <td class="border px-4 py-2 text-right">$220.00</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="text-sm text-slate-500 border-t pt-6 sm:pt-8 mt-6 sm:mt-8">
            <p>Payment is due within 30 days of receipt.</p>
            <p>Bank Account: 123456789, Routing Number: 000111222</p>
            <p>Thank you for your business!</p>
        </div>
    </div>
</x-filament-panels::page>
