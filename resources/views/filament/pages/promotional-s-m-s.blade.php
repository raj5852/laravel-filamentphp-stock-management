<x-filament-panels::page>

    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex">
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="submit" icon="">
                <span wire:loading.remove wire:target="submit">Send</span>
                <span wire:loading wire:target="submit">Sending...</span>
            </x-filament::button>
        </div>
    </form>


</x-filament-panels::page>
