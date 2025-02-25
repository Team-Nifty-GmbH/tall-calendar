<div class="space-y-8 divide-y divide-gray-200">
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-1">
            <div x-cloak x-show="$wire.selectedCalendar.children == 0">
                <x-select.styled
                    wire:model="selectedCalendar.parentId"
                    :label="__('Parent Calendar')"
                    :options="$this->parentCalendars"
                    select="label:name|value:id"
                    option-description="description"
                />
            </div>
            <x-input wire:model="selectedCalendar.name" :label="__('Calendar Name')"/>
            <x-input
                class="p-0"
                type="color"
                :label="__('Color')"
                wire:model="selectedCalendar.color"
            />
            <x-checkbox wire:model="selectedCalendar.hasRepeatableEvents" :label="__('Has repeatable events')"/>
            <x-checkbox wire:model="selectedCalendar.isPublic" :label="__('Public')"/>
        </div>
    </div>
</div>
