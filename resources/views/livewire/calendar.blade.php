<div x-data="{
        ...tallCalendar(),
        calendarItem: {},
        editCalendar(calendar) {
            this.calendarItem = calendar;
            $openModal(document.getElementById(this.id + '-calendar-edit'))
        },
        saveCalendar() {
            this.$wire.saveCalendar(this.calendarItem).then(calendar => {
                this.calendars.splice(this.calendars.findIndex(c => c.id === calendar.id), 1, calendar);
                this.calendarId = calendar.id;
                this.activeCalendars.push(calendar.id);

                this.close();
            });
        },
        deleteCalendar() {
            this.$wire.deleteCalendar(this.calendarItem).then(success => {
                if(success) {
                    this.close();
                }

                this.calendar.getEventSourceById(this.calendarItem.id).remove();
                this.calendars.splice(this.calendars.findIndex(c => c.id === this.calendarItem.id), 1);
            });
        },
        eventClick(event) {
            this.calendarEvent = event.event;
            this.calendarId = event.event.extendedProps.calendar_id;
        },
    }"
     x-on:edit-calendar="editCalendar($event.detail)"
     x-on:calendar-event-click="eventClick($event.detail)"
>
    <x-modal.card x-bind:id="id + '-calendar-edit'" :title="__('Edit Calendar')">
        <x-tall-calendar::calendar-edit />
        <x-slot name="footer">
            <div class="flex justify-between gap-x-4">
                <x-button flat negative :label="__('Delete')" x-on:click="deleteCalendar()" />

                <div class="flex">
                    <x-button flat :label="__('Cancel')" x-on:click="close" />
                    <x-button primary :label="__('Save')" x-on:click="saveCalendar()" />
                </div>
            </div>
        </x-slot>
    </x-modal.card>
    <x-card padding="none" class="lg:flex whitespace-nowrap">
        @if($showCalendars)
                <div class="p-1.5 space-y-4 border-r dark:border-secondary-600">
                    <div x-data="{show: true}">
                        <div class="flex justify-between items-center group">
                            <div class="flex items-center">
                                <span class="font-semibold dark:text-gray-50 pr-1.5">{{ __('My Calendars') }}</span>
                                <svg x-on:click="editCalendar({})" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="cursor-pointer invisible group-hover:visible w-5 h-5">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zM12.75 9a.75.75 0 00-1.5 0v2.25H9a.75.75 0 000 1.5h2.25V15a.75.75 0 001.5 0v-2.25H15a.75.75 0 000-1.5h-2.25V9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <svg x-on:click="show = ! show" x-bind:class="show || '-rotate-90'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                        <div x-cloak x-show="show">
                            <x-tall-calendar::calendar-list group="my" />
                        </div>
                    </div>
                    <div x-data="{show: true}">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold dark:text-gray-50 pr-1.5">{{ __('Shared with me') }}</span>
                            <svg x-on:click="show = ! show" x-bind:class="show || '-rotate-90'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                        <div x-cloak x-show="show">
                            <x-tall-calendar::calendar-list group="shared" />
                        </div>
                    </div>
                    <div x-data="{show: true}">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold dark:text-gray-50 pr-1.5">{{ __('Public') }}</span>
                            <svg x-on:click="show = ! show" x-bind:class="show || '-rotate-90'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </div>
                        <div x-cloak x-show="show">
                            <x-tall-calendar::calendar-list group="public" />
                        </div>
                    </div>
                </div>
        @endif
        <div wire:ignore class="dark:text-gray-50" x-bind:id="id"></div>
    </x-card>
</div>
