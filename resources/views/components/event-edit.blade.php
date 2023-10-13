<div class="grid grid-cols-1 gap-1.5" x-data="{errors: $wire.entangle('validationErrors')}">
    @section('event-edit.content')
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
        <x-checkbox :label="__('repeatable')" x-model="calendarEvent.is_repeatable" x-bind:disabled="! calendarEvent.is_editable ?? false"/>
        <div x-show="calendarEvent.is_repeatable">
            <div class="grid grid-cols-3 items-center gap-1.5">
                <x-label>
                    {{ __('Repeat') }}
                </x-label>
                <x-inputs.number x-model="calendarEvent.interval" :min="1" x-bind:disabled="! calendarEvent.is_editable ?? false" />
                <x-select
                    x-on:selected="calendarEvent.unit = $event.detail.value"
                    x-init="$watch('calendarEvent.unit', (value) => {
                        const option = options.find(option => option.value === value);
                        if (option) {
                            select(option);
                        }
                    })"
                    :clearable="false"
                    :options="[
                        ['label' => __('Day(s)'), 'value' => 'days'],
                        ['label' => __('Week(s)'), 'value' => 'weeks'],
                        ['label' => __('Month(s)'), 'value' => 'months'],
                        ['label' => __('Year(s)'), 'value' => 'years'],
                    ]"
                    option-label="label"
                    option-value="value"
                    x-bind:disabled="! calendarEvent.is_editable ?? false"
                />
            </div>

            <template x-if="calendarEvent.unit === 'weeks'">
                <div class="grid grid-cols-7 items-center gap-1.5 mt-4">
                    <x-button
                        rounded
                        primary
                        flat
                        xs
                        :label="__('Mon')"
                        x-on:click="calendarEvent.weekdays.indexOf('Mon') !== -1 ? calendarEvent.weekdays = calendarEvent.weekdays.filter((day) => day !== 'Mon') : calendarEvent.weekdays.push('Mon')"
                        x-bind:class="calendarEvent.weekdays.indexOf('Mon') !== -1 ? 'bg-primary-500 text-white' : ''"
                    />
                    <x-button
                        rounded
                        primary
                        flat
                        xs
                        :label="__('Tue')"
                        x-on:click="calendarEvent.weekdays.indexOf('Tue') !== -1 ? calendarEvent.weekdays = calendarEvent.weekdays.filter((day) => day !== 'Tue') : calendarEvent.weekdays.push('Tue')"
                        x-bind:class="calendarEvent.weekdays.indexOf('Tue') !== -1 ? 'bg-primary-500 text-white' : ''"
                    />
                    <x-button
                        rounded
                        primary
                        flat
                        xs
                        :label="__('Wed')"
                        x-on:click="calendarEvent.weekdays.indexOf('Wed') !== -1 ? calendarEvent.weekdays = calendarEvent.weekdays.filter((day) => day !== 'Wed') : calendarEvent.weekdays.push('Wed')"
                        x-bind:class="calendarEvent.weekdays.indexOf('Wed') !== -1 ? 'bg-primary-500 text-white' : ''"
                    />
                    <x-button
                        rounded
                        primary
                        flat
                        xs
                        :label="__('Thu')"
                        x-on:click="calendarEvent.weekdays.indexOf('Thu') !== -1 ? calendarEvent.weekdays = calendarEvent.weekdays.filter((day) => day !== 'Thu') : calendarEvent.weekdays.push('Thu')"
                        x-bind:class="calendarEvent.weekdays.indexOf('Thu') !== -1 ? 'bg-primary-500 text-white' : ''"
                    />
                    <x-button
                        rounded
                        primary
                        flat
                        xs
                        :label="__('Fri')"
                        x-on:click="calendarEvent.weekdays.indexOf('Fri') !== -1 ? calendarEvent.weekdays = calendarEvent.weekdays.filter((day) => day !== 'Fri') : calendarEvent.weekdays.push('Fri')"
                        x-bind:class="calendarEvent.weekdays.indexOf('Fri') !== -1 ? 'bg-primary-500 text-white' : ''"
                    />
                    <x-button
                        rounded
                        primary
                        flat
                        xs
                        :label="__('Sat')"
                        x-on:click="calendarEvent.weekdays.indexOf('Sat') !== -1 ? calendarEvent.weekdays = calendarEvent.weekdays.filter((day) => day !== 'Sat') : calendarEvent.weekdays.push('Sat')"
                        x-bind:class="calendarEvent.weekdays.indexOf('Sat') !== -1 ? 'bg-primary-500 text-white' : ''"
                    />
                    <x-button
                        rounded
                        primary
                        flat
                        xs
                        :label="__('Sun')"
                        x-on:click="calendarEvent.weekdays.indexOf('Sun') !== -1 ? calendarEvent.weekdays = calendarEvent.weekdays.filter((day) => day !== 'Sun') : calendarEvent.weekdays.push('Sun')"
                        x-bind:class="calendarEvent.weekdays.indexOf('Sun') !== -1 ? 'bg-primary-500 text-white' : ''"
                    />
                </div>
            </template>
            <template x-if="calendarEvent.unit === 'months'">
                <x-select class="mt-4"
                    x-on:selected="calendarEvent.monthly = $event.detail.value"
                    x-init="$watch('calendarEvent.monthly', (value) => {
                        const option = options.find(option => option.value === value);
                        if (option) {
                            select(option);
                        }
                    })"
                    :clearable="false"
                    x-bind:disabled="! calendarEvent.is_editable ?? false"
                >
                    <x-select.option value="day">
                        <span x-text="'{{ __('Monthly at') }} ' + dayjs(calendarEvent.start).format('DD') + '.'"></span>
                    </x-select.option>
                    <x-select.option value="first">
                        <span x-text="'{{ __('Monthly at first') }} ' + dayjs(calendarEvent.start).format('dddd')"></span>
                    </x-select.option>
                    <x-select.option value="second">
                        <span x-text="'{{ __('Monthly at second') }} ' + dayjs(calendarEvent.start).format('dddd')"></span>
                    </x-select.option>
                    <x-select.option value="third">
                        <span x-text="'{{ __('Monthly at third') }} ' + dayjs(calendarEvent.start).format('dddd')"></span>
                    </x-select.option>
                    <x-select.option value="fourth">
                        <span x-text="'{{ __('Monthly at fourth') }} ' + dayjs(calendarEvent.start).format('dddd')"></span>
                    </x-select.option>
                    <x-select.option value="last">
                        <span x-text="'{{ __('Monthly at last') }} ' + dayjs(calendarEvent.start).format('dddd')"></span>
                    </x-select.option>
                </x-select>
            </template>

            <x-label class="mt-2 mb-4">
                {{ __('Repeat end') }}
            </x-label>
            <x-radio :label="__('Never')" :value="null" x-model="calendarEvent.repeat_radio" x-bind:disabled="! calendarEvent.is_editable ?? false" />
            <div class="grid grid-cols-2 items-center gap-1.5">
                <x-radio :label="__('At')" value="repeat_end" x-model="calendarEvent.repeat_radio" x-bind:disabled="! calendarEvent.is_editable ?? false" />
                <x-input
                    id="calendar-event-repeat-end-date"
                    type="date"
                    x-bind:disabled="(! calendarEvent.is_editable ?? false) || calendarEvent.repeat_radio !== 'repeat_end'"
                    x-bind:value="dayjs(calendarEvent.repeat_end).format('YYYY-MM-DD')"
                    x-on:change="setDateTime('repeat_end', $event)"
                />
                <x-radio :label="__('After amount of events')" value="recurrences" x-model="calendarEvent.repeat_radio" x-bind:disabled="! calendarEvent.is_editable ?? false" />
                <x-inputs.number x-model="calendarEvent.recurrences" x-bind:disabled="(! calendarEvent.is_editable ?? false) || calendarEvent.repeat_radio !== 'recurrences'" />
            </div>
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
    @show
</div>
