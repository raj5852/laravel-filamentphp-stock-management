<div>

    <div class="grid gap-4 md:grid-cols-2">

        <div>
            <div class="space-y-2">
                <div
                    class="max-w p-6 bg-white border border-gray-200 rounded-lg shadow-sm dark:bg-gray-900 dark:border-gray-700">
                    {{ $this->form }}

                </div>
            </div>


        </div>

        <div class="">
            {{ $this->table }}
        </div>

        <x-filament-actions::modals />
    </div>
