<div class="grid grid-cols-3 gap-4 p-4">
    <!-- Product Image -->
    <div class="col-span-1 flex items-center justify-center">
        <img src="{{ $product->product_image ? Storage::url($product->product_image) : '/images/notfound.jpg' }}" alt="Product Image"
            class="w-32 h-32 object-cover">
    </div>

    <!-- Product Details -->
    <div class="col-span-2">
        <h2 class="text-xl font-semibold">{{ $product->name }}</h2>
        <table class="w-full border-collapse border border-gray-100 mt-2">
            <tr class="border">
                <td class="p-2 font-semibold">Code</td>
                <td class="p-2">{{ $product->product_code }}</td>
            </tr>
            <tr class="border">
                <td class="p-2 font-semibold">Category</td>
                <td class="p-2">{{ $product->category?->name }}</td>
            </tr>
            <tr class="border">
                <td class="p-2 font-semibold">Brand</td>
                <td class="p-2">{{ $product->brand?->brand_name }}</td>
            </tr>
            <tr class="border">
                <td class="p-2 font-semibold">Price</td>
                <td class="p-2">{{ number_format($product->sale_price,'2', '.', '' ) }}</td>
            </tr>
            <tr class="border">
                <td class="p-2 font-semibold">Cost</td>
                <td class="p-2">{{ number_format($product->purchase_cost, 2, '.', '') }}</td>
            </tr>
            <tr class="border">
                <td class="p-2 font-semibold">Stock</td>
                <td class="p-2">{{ $product->productdetails?->available_stock_in_text }}</td>
            </tr>
            <tr class="border">
                <td class="p-2 font-semibold">Details</td>
                <td class="p-2">

                    {!! $product->product_details !!}
                </td>
            </tr>
        </table>
    </div>



</div>
