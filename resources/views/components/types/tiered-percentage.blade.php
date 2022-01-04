<div>
    <x-fab::lists.table>
        <x-fab::lists.table.header>Threshold</x-fab::lists.table.header>
        <x-fab::lists.table.header>Percentage</x-fab::lists.table.header>
        <x-fab::lists.table.header></x-fab::lists.table.header>

        @foreach($displayTiers as $index => $tier)
            <x-fab::lists.table.row :odd="$loop->odd">
                <x-fab::lists.table.column>
                    <x-fab::forms.input
                        leading="$"
                        wire:model.debounce.1s="displayTiers.{{ $index }}.threshold"
                    />
                </x-fab::lists.table.column>
                <x-fab::lists.table.column>
                    <x-fab::forms.input
                        trailing="%"
                        wire:model.debounce.1s="displayTiers.{{ $index }}.value"
                    />
                </x-fab::lists.table.column>
                <x-fab::lists.table.column align="right">
                    <x-fab::elements.button size="xs" wire:click="removeTier({{ $index }})">
                        <x-fab::elements.icon icon="x" type="solid" class="h-6 w-3" />
                    </x-fab::elements.button>
                </x-fab::lists.table.column>
            </x-fab::lists.table.row>
        @endforeach
    </x-fab::lists.table>

    <x-fab::elements.button
        wire:click="addTier"
        class="mt-4"
    >
        Add Tier
    </x-fab::elements.button>
</div>
