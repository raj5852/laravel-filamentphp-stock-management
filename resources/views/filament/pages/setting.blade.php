<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex">
            <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="submit" icon="">
                <span wire:loading.remove wire:target="submit">Save Settings</span>
                <span wire:loading wire:target="submit">Saving...</span>
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
