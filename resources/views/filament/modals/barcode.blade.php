<div class="text-center">
    <div id="qr-code-container" class="mt-6">
        {{-- @dd(request()->header('user-agent')) --}}
        @php
            // Make Barcode object of Code128 encoding.
            $barcode = (new Picqer\Barcode\Types\TypeCode128())->getBarcode($record['product_code']);

            // Output the barcode as HTML in the browser with an HTML Renderer
            $renderer = new Picqer\Barcode\Renderers\HtmlRenderer();

            // Set the foreground color based on the theme
            $color = [200, 200, 200];

            $renderer->setForegroundColor($color);
            $data = $renderer->render($barcode);
        @endphp
        <center>
            {!! $data !!}
            <div style="line-height: 14px !important;">
                <span style="font-size: 12px;">{{ $record['product_code'] }}</span>

            </div>
            <div style="line-height: 10px !important;">
                <span style="font-size: 12px;"><b>{{ $record['product_name'] }}</b></span>
            </div>
            <div style="line-height: 16px !important;">
                <span style="font-size: 12px;"><b>{{ number_format($record['sale_price'],2, '.', '') }} TK</b></span>
            </div>

        </center>
    </div>

    <x-filament::button class="mt-6" color="primary" tag="button" >
        <x-slot:icon>
            <x-fas-print/>
        </x-slot:icon>
        Print
</x-filament::button>


</div>
