<div class="space-y-8 divide-y divide-gray-200">
    <div class="space-y-8 divide-y divide-gray-200">
        <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
            <div class="sm:col-span-6">
                <x-input x-model="calendarItem.name" :label="__('Calendar Name')"/>
            </div>
            <div class="sm:col-span-6">
                <x-input
                    class="p-0"
                    type="color"
                    :label="__('Color')"
                    x-model="calendarItem.color"
                />
            </div>
            <div class="sm:col-span-6">
                <x-checkbox x-model="calendarItem.isPublic" :label="__('Public')"/>
            </div>
        </div>
    </div>
</div>
