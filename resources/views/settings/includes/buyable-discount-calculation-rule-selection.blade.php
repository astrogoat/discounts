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
