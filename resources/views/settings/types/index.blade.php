<div>
    <x-fab::forms.select
        wire:model="payload.type"
        name="payload[type]"
        label="Type"
        class="mb-4"
    >
        @foreach($this->getTypes() as $key => $type)
            <option value="{{ $key }}">{{ $type->getName() }}</option>
        @endforeach
    </x-fab::forms.select>

    @include($this->getSelectedTypeInclude())
</div>
