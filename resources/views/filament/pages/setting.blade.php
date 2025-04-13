<x-filament::page>
    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="flex">
            <x-filament::button type="submit" icon="">
                Save Settings
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
