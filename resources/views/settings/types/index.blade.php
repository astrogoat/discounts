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

    <x-fab::forms.input
        wire:model="payload.legal_link_copy"
        name="payload[legal_link_copy]"
        label="Legal link copy"
        class="mb-4 mt-4"
    />

    <x-fab::forms.editor
        wire:model="payload.legal"
        name="payload[legal]"
        label="Legal copy"
        class="mb-4 mt-4"
    />
</div>
