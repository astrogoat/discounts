<div>
    <x-fab::lists.table>
        <x-fab::lists.table.header>Threshold</x-fab::lists.table.header>
        <x-fab::lists.table.header>Amount</x-fab::lists.table.header>
        <x-fab::lists.table.header></x-fab::lists.table.header>

        @foreach($displayTiers as $index => $tier)
            <x-fab::lists.table.row :odd="$loop->odd">
                <x-fab::lists.table.column>
                    <x-fab::forms.input
                        leading="$"
                        wire:model="displayTiers.{{ $index }}.threshold"
                    />
                </x-fab::lists.table.column>
                <x-fab::lists.table.column>
                    <x-fab::forms.input
                        leading="$"
                        wire:model="displayTiers.{{ $index }}.value"
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

    <x-fab::forms.select
        id="buyableDiscountCalculationRule"
        class="mt-4"
        label="Calculate discount for items about to be added"
        wire:model="buyableDiscountCalculationRule"
        help="{{ $this->buyableDiscountCalculationRuleHelp() }}"
    >
        <option value="itemsInCart">Dynamic - Based on items in cart</option>
        <option value="currentTier">Static - Based on current discount tier</option>
        <option value="highestTier">Static - Based on highest discount tier</option>
    </x-fab::forms.select>
</div>
