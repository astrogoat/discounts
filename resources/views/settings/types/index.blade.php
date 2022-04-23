<div>
    <x-fab::forms.select
        wire:model="payload.type"
        name="payload[type]"
        label="Type"
        class="mb-4"
    >
        @foreach($this->getTypes() as $type)
            <option value="{{ $type::class }}">{{ $type->getTypeName() }}</option>
        @endforeach
    </x-fab::forms.select>

    @include($this->getSelectedTypeView(), ['component' => $this->getSelectedType()::getName()])
</div>
