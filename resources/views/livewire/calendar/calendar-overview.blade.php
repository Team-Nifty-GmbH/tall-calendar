<x-card padding="none" class="lg:flex whitespace-nowrap">
    <div>
        @if($showCalendars)
            @section('calendar-modal')
                <x-modal name="calendar-modal">
                    <x-card :title="__('Edit Calendar')">
                        @section('calendar-edit')
                            <x-tall-calendar::calendar-edit />
                        @show
                        <x-slot name="footer">
                            <div class="flex justify-between gap-x-4">
                                <div>
                                    <x-button x-show="$wire.selectedCalendar.id" flat negative :label="__('Delete')" x-on:click="deleteCalendar()" />
                                </div>
                                <div class="flex">
                                    <x-button flat :label="__('Cancel')" x-on:click="close();" />
                                    <x-button primary :label="__('Save')" x-on:click="saveCalendar()" />
                                </div>
                            </div>
                        </x-slot>
                    </x-card>
                </x-modal>
            @show
            @section('calendar-overview')
                <div class="p-1.5 space-y-4">
                    @section('calendar-overview.items')
                        @foreach($calendarGroups ?? [] as $group => $label)
                            <div x-data="{show: true}" @if($group !== 'my') x-cloak x-show="calendars?.filter(calendar => calendar.group === '{{ $group }}').length > 0" @endif>
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold dark:text-gray-50 pr-1.5">{{ $label }}</span>
                                    <svg x-on:click="show = ! show" x-bind:class="show || '-rotate-90'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </div>
                                <div x-cloak x-collapse x-show="show">
                                    <x-tall-calendar::calendar-list :$group />
                                </div>
                            </div>
                        @endforeach
                    @show
                </div>
            @show
        @endif
        @if($showInvites)
            @section('invites')
                <div x-data="{tab: {name: 'new', status: [null]}}" x-cloak x-show="invites.length > 0" class="p-1.5 space-y-4">
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
            @show
        @endif
    </div>
    <div wire:ignore class="w-full">
        <div class="dark:text-gray-50 border-l dark:border-secondary-600" x-bind:id="id"></div>
    </div>
</x-card>
