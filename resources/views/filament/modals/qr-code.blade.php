<div class="text-center">
    <div id="qr-code-container" class="mt-6">
        <center>
            {!! $qrCode !!}
        </center>
    </div>

    <x-filament::button class="mt-6" color="primary" tag="button" >
        <x-slot:icon>
            <x-fas-print/>
        </x-slot:icon>
        Print
</x-filament::button>


</div>

