<div class="border rounded-lg p-4 shadow-sm">
   @dd($record)
    <div class="flex flex-col items-center">
        <div class="bg-gray-100 p-4 rounded">
            <img src="{{ $record->image ?? asset('placeholder.png') }}"
                 alt="{{ $record->product_name }}"
                 class="w-24 h-24 object-cover">
        </div>
        <h3 class="mt-4 text-center font-bold">{{ $record->product_name }}</h3>
        <p class="mt-2 text-sm text-gray-600">{{ $record->product_name }}</p>
        <p class="mt-2 text-lg font-semibold">{{ $record->product_name }} Tk</p>
        <p class="mt-1 text-sm text-gray-500">Stock: {{ $record->product_name }}</p>
    </div>
</div>
