<div tall-calendar x-data="{
        ...tallCalendar(),
        @section('calendar-data')
        @show
    }"
     x-on:edit-calendar="editCalendar($event.detail)"
>
    <div>
        @section('calendar-event-modal')
            <x-modal-card name="calendar-event-modal" :title="__('Edit Event')">
                <x-tall-calendar::event-edit />
                <x-slot name="footer">
                    <div class="flex justify-between gap-x-4">
                        <div>
                            <x-button
                                x-show="calendarEvent.id"
                                spinner
                                flat
                                negative
                                :label="__('Delete')"
                                x-show="$wire.calendarEvent.is_editable && $wire.calendarEvent.id"
                                x-on:click="$wireui.confirmDialog({
                                    id: 'delete-event-dialog',
                                    icon: 'question',
                                    accept: {
                                        label: '{{ __('OK') }}',
                                        execute: () => deleteEvent()
                                    },
                                    reject: {
                                        label: '{{ __('Cancel') }}',
                                    }
                                })"
                            />
                        </div>
                        <div class="flex">
                            <x-button flat :label="__('Cancel')" x-on:click="close" />
                            <x-button
                                primary
                                :label="__('Save')"
                                x-on:click="
                                $wire.confirmSave = $wire.calendarEventWasRepeatable && !$wire.calendarEvent.has_repeats ? 'this' : 'future';
                                $wire.calendarEventWasRepeatable ?
                                    $wireui.confirmDialog({
                                        id: 'edit-repeatable-event-dialog',
                                        icon: 'question',
                                        accept: {
                                            label: '{{ __('OK') }}',
                                            execute: () => saveEvent()
                                        },
                                        reject: {
                                            label: '{{ __('Cancel') }}',
                                        }
                                    }) :
                                    saveEvent();
                                "
                            />
                        </div>
                    </div>
                </x-slot>
            </x-modal-card>
            <x-dialog id="edit-repeatable-event-dialog" :title="__('Edit Repeatable Event')">
                <div x-show="! $wire.calendarEvent.has_repeats">
                    <x-radio :label="__('This event')" value="this" wire:model="confirmSave"/>
                </div>
                <x-radio :label="__('This event and following')" value="future" wire:model="confirmSave"/>
                <x-radio :label="__('All events')" value="all" wire:model="confirmSave"/>
            </x-dialog>
            <x-dialog id="delete-event-dialog" :title="__('Confirm Delete Event')">
                <div x-show="$wire.calendarEventWasRepeatable">
                    <x-radio :label="__('This event')" value="this" wire:model="confirmDelete"/>
                    <x-radio :label="__('This event and following')" value="future" wire:model="confirmDelete"/>
                    <x-radio :label="__('All events')" value="all" wire:model="confirmDelete"/>
                </div>
            </x-dialog>
        @show
    </div>
    <x-card padding="none">
        <div class="lg:flex whitespace-nowrap">
            <div>
                @if($showCalendars)
                    <x-modal-card name="calendar-modal" x-on:close="resetCalendarItem();" :title="__('Edit Calendar')">
                        <x-tall-calendar::calendar-edit />
                        <x-slot name="footer">
                            <div class="flex justify-between gap-x-4">
                                <div>
                                    <x-button x-show="calendarItem.id" flat negative :label="__('Delete')" x-on:click="deleteCalendar()" />
                                </div>
                                <div class="flex">
                                    <x-button flat :label="__('Cancel')" x-on:click="close();" />
                                    <x-button primary :label="__('Save')" x-on:click="saveCalendar()" />
                                </div>
                            </div>
                        </x-slot>
                    </x-modal-card>
                    @section('calendar-list')
                    <div class="p-1.5 space-y-4">
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
                    @show
                @endif
                @if($showInvites)
                    <div x-data="{tab: {name: 'new', status: [null]}}" class="p-1.5 space-y-4">
                        <div class="flex justify-between pb-1.5 font-semibold dark:text-gray-50">
                            <div>{{ __('Invites') }}</div>
                        </div>
                        <div>
                            <div class="pb-2.5">
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                        <div x-on:click="tab = {name: 'new', status: [null]}" x-bind:class="{'border-indigo-500 text-indigo-600' : tab.name === 'new'}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-xs text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-50">{{ __('New') }}</div>
                                        <div x-on:click="tab = {name: 'accepted', status: ['accepted', 'maybe']}" x-bind:class="{'border-indigo-500 text-indigo-600' : tab.name === 'accepted'}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-xs text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-50">{{ __('Accepted') }}</div>
                                        <div x-on:click="tab = {name: 'declined', status: ['declined']}" x-bind:class="{'border-indigo-500 text-indigo-600' : tab.name === 'declined'}" class="cursor-pointer whitespace-nowrap border-b-2 border-transparent py-4 px-1 text-xs text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-50">{{ __('Declined') }}</div>
                                    </nav>
                                </div>
                            </div>
                        </div>
                        <div x-show="invites.length">
                            <div class="space-y-3">
                                <template x-for="invite in invites.filter((invite) => tab.status.includes(invite.status))">
                                    <div class="rounded-md bg-gray-100 p-2 shadow-md">
                                        <div>
                                            <div x-text="invite.calendar_event.title"></div>
                                            <div
                                                x-text="parseDateTime(invite.calendar_event, '{{ app()->getLocale() }}', 'start')"
                                            >
                                            </div>
                                        </div>
                                        <div class="pt-1.5">
                                            <x-button x-show="invite?.status !== 'declined'" x-on:click="inviteStatus(invite, 'declined')" 2xs negative :label="__('Decline')"></x-button>
                                            <x-button x-show="invite?.status !== 'maybe'" x-on:click="inviteStatus(invite, 'maybe')" 2xs warning :label="__('Maybe')"></x-button>
                                            <x-button x-show="invite?.status !== 'accepted'" x-on:click="inviteStatus(invite, 'accepted')" 2xs positive :label="__('Accept')"></x-button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div wire:ignore>
                <div class="dark:text-gray-50 border-l dark:border-secondary-600" x-bind:id="id"></div>
            </div>
        </div>
    </x-card>
</div>
