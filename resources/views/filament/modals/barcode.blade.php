<div class="text-center">
    <div id="bar-code-container" class="mt-6">


        <center>
            <span style="font-size: 12px">{{ $company_name }} </span>
            <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $record['product_code'] }}&code=Code128&scale=2" alt="" >

            <div style="line-height: 10px !important;">
                <span style="font-size: 12px;"><b>{{ $record['product_name'] }}</b></span>
            </div>
            <div style="line-height: 16px !important;">
                <span style="font-size: 12px;"><b>{{ number_format($record['sale_price'],2, '.', '') }} TK</b></span>
            </div>

        </center>
    </div>

    <x-filament::button onclick="printBarCode()" class="mt-6" color="primary" tag="button" >
        <x-slot:icon>
            <x-fas-print/>
        </x-slot:icon>
        Print
</x-filament::button>


</div>
