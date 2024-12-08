<div tall-calendar x-data="{
        ...tallCalendar(),
        @section('calendar-data')
        @show
    }"
>
    <div>
        @section('calendar-event-modal')
            <x-modal name="calendar-event-modal">
                <x-card :title="__('Edit Event')">
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
                </x-card>
            </x-modal>
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
    <livewire:calendar-overview :show-calendars="$showCalendars" :show-invites="$showInvites" />
</div>
