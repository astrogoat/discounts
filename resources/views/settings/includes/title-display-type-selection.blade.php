<x-fab::forms.select
    label="Display title as"
    class="mt-4"
    wire:model="titleDisplayTypeAs"
    id="titleDisplayType"
>
    @foreach($this->getTitleDisplayTypeOptions() as $key => $value)
        <option value="{{ $key }}">{{ $value }}</option>
    @endforeach
</x-fab::forms.select>
