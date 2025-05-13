<x-filament-panels::page>


<form wire:submit.prevent="submit" class="space-y-6">
    {{ $this->form }}

    <div class="flex">
        <x-filament::button form="submit" type="submit">
            Payment
        </x-filament::button>
    </div>
</form>
</x-filament-panels::page>
