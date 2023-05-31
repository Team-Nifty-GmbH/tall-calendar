<div class="grid grid-cols-1 gap-1.5" x-data="{errors: $wire.entangle('validationErrors')}">
    <x-input x-ref="autofocus" :label="__('Title') . '*'" x-model="calendarEvent.title" x-bind:readonly="! calendarEvent.is_editable ?? false"/>
    <x-textarea :label="__('Description')" x-model="calendarEvent.description" x-bind:readonly="! calendarEvent.is_editable ?? false"/>
    <x-checkbox :label="__('all-day')" x-model="calendarEvent.allDay" x-bind:disabled="! calendarEvent.is_editable ?? false"/>
    <div class="grid grid-cols-3 items-center gap-1.5">
        <x-label>
            {{__('Start')}}
        </x-label>
        <x-input
            id="calendar-event-start-date"
            type="date"
            x-bind:disabled="! calendarEvent.is_editable ?? false"
            x-bind:value="dayjs(calendarEvent.start).format('YYYY-MM-DD')"
            x-on:change="setDateTime('start', $event)"
        />
        <x-input
            id="calendar-event-start-time"
            x-show="! calendarEvent.allDay"
            type="time"
            x-bind:disabled="! calendarEvent.is_editable ?? false"
            x-on:change="setDateTime('start', $event)"
            x-bind:value="dayjs(calendarEvent.start).format('HH:mm')"
        />
    </div>
    <div class="grid grid-cols-3 items-center gap-1.5">
        <x-label>
            {{__('End')}}
        </x-label>
        <x-input
            id="calendar-event-end-date"
            type="date"
            x-bind:disabled="! calendarEvent.is_editable ?? false"
            x-bind:value="dayjs(calendarEvent.end).format('YYYY-MM-DD')"
            x-on:change="setDateTime('end', $event)"
        />
        <x-input
            id="calendar-event-end-time"
            x-show="! calendarEvent.allDay"
            type="time"
            x-bind:disabled="! calendarEvent.is_editable ?? false"
            x-on:change="setDateTime('end', $event)"
            x-bind:value="dayjs(calendarEvent.end).format('HH:mm')"
        />
    </div>
    <div x-show="calendarEvent.is_invited">
        <x-select x-model="calendarEvent.status" x-init="$watch('calendarEvent.status', (value) => {
                const option = options.find(option => option.value === value);
                if (option) {
                    select(option);
                } else {
                    clear();
                }
            })" :label="__('My status')" :clearable="false">
            <x-select.option value="accepted">
                <div>
                    <x-button.circle
                        disabled
                        positive
                        xs
                        icon="check"
                    />{{__('Accepted')}}
                </div>
            </x-select.option>
            <x-select.option :label="__('Declined')" value="declined">
                <div>
                    <x-button.circle
                        disabled
                        negative
                        xs
                        icon="x"
                    />{{__('Declined')}}
                </div>
            </x-select.option>
            <x-select.option :label="__('Maybe')" value="maybe">
                <div>
                    <x-button.circle
                        disabled
                        warning
                        xs
                        label="?"
                    />{{__('Maybe')}}
                </div>
            </x-select.option>
        </x-select>
    </div>
    <div>
        <div class="grid grid-cols-1 gap-1.5" x-show="calendarEvent.is_editable ?? false" x-on:click.outside="search = false">
            <x-label for="invite" :label="__('Invites')" />
            <template x-for="invited in calendarEvent.invited">
                <div class="flex gap-1.5">
                    <x-button.circle
                        negative
                        xs
                        icon="trash"
                        x-bind:disabled="! calendarEvent.is_editable ?? false"
                        x-on:click="calendarEvent.invited.splice(calendarEvent.invited.indexOf(invited), 1)"
                    />
                    <template x-if="invited.pivot?.status === 'accepted'">
                        <x-button.circle
                            disabled
                            positive
                            xs
                            icon="check"
                        />
                    </template>
                    <template x-if="invited.pivot?.status === 'declined'">
                        <x-button.circle
                            disabled
                            negative
                            xs
                            icon="x" />
                    </template>
                    <template x-if="invited.pivot?.status === 'maybe'">
                        <x-button.circle
                            disabled
                            warning
                            xs
                            label="?"
                        />
                    </template>
                    <template x-if="invited.pivot?.status !== 'accepted' && invited.pivot?.status !== 'declined' && invited.pivot?.status !== 'maybe'">
                        <x-button.circle
                            disabled
                            secondary
                            xs
                            label="?"
                        />
                    </template>
                    <x-badge md x-text="invited.label" />
                </div>
            </template>
            <x-select
                id="invite"
                option-value="id"
                option-label="label"
                :placeholder="__('Add invite')"
                :template="[
                                'name'   => 'user-option',
                            ]"
                :async-data="[
                                'api' => route('search', \FluxErp\Models\User::class),
                                'method' => 'POST',
                                'params' => [
                                    'with' => 'media',
                                    'where' => [
                                        [
                                            'id',
                                            '!=',
                                            auth()->user()->id
                                        ]
                                    ],
                                ]
                            ]"
                x-on:selected="calendarEvent.invited.push($event.detail); clear(); asyncData.params.where.push(['id', '!=', $event.detail.id])"
            />
        </div>
    </div>
</div>
