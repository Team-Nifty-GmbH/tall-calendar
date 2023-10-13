<?php

namespace TeamNiftyGmbH\Calendar;

use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use TeamNiftyGmbH\Calendar\Models\Pivot\Inviteable;
use WireUi\Traits\Actions;

class CalendarComponent extends Component
{
    use Actions;

    public bool $showCalendars = true;

    public bool $showInvites = true;

    public array $calendarEvent = [];

    public array $validationErrors = [];

    public ?string $confirmOption = null;

    private Collection $sharedWithMe;

    private Collection $myCalendars;

    public function mount(): void
    {
        $this->calendarEvent = ['start' => now(), 'end' => now()];
    }

    public function getRules(): array
    {
        return [
            'title' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date|after:start',
            'description' => 'nullable|string',

            'is_repeatable' => 'boolean',
            'interval' => 'required_if:is_repeatable,true|integer|min:1',
            'unit' => 'required_if:is_repeatable,true|in:days,weeks,months,years',
            'weekdays' => 'exclude_unless:unit,weeks|required_if:unit,weeks|array',
            'weekdays.*' => 'required|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'monthly' => 'exclude_unless:unit,months|required_if:unit,months|in:day,first,second,third,fourth,last',
            'repeat_radio' => 'nullable|in:repeat_end,recurrences',
            'repeat_end' => [
                'exclude_unless:repeat_radio,repeat_end',
                'required_if:repeat_radio,repeat_end',
                'date',
                'after:start'
            ],
            'recurrences' => [
                'exclude_unless:repeat_radio,recurrences',
                'required_if:repeat_radio,recurrences',
                'integer',
                'min:1',
            ],
        ];
    }

    public function render(): Factory|Application|View
    {
        return view('tall-calendar::livewire.calendar');
    }

    public function getEvents(array $info, array $calendarAttributes): array
    {
        $calendar = config('tall-calendar.models.calendar')::query()->find($calendarAttributes['id']);

        $calendarEvents = $calendar->calendarEvents()
            ->whereNull('repeat')
            ->where(function ($query) use ($info) {
                $query->whereBetween('start', [
                    Carbon::parse($info['start']),
                    Carbon::parse($info['end']),
                ])
                    ->orWhereBetween('end', [
                        Carbon::parse($info['start']),
                        Carbon::parse($info['end']),
                    ]);
            })
            ->with('invited', fn ($query) => $query->withPivot('status'))
            ->get()
            ->merge(
                $calendar->invitesCalendarEvents()
                    ->addSelect('calendar_events.*')
                    ->addSelect('inviteables.status')
                    ->addSelect('inviteables.model_calendar_id AS calendar_id')
                    ->whereIn('inviteables.status', ['accepted', 'maybe'])
                    ->get()
                    ->each(fn ($event) => $event->is_invited = true)
            );

        return $this->calculateRepeatableEvents($calendar, $info, $calendarEvents)
            ->map(function ($event) use ($calendarAttributes) {
                $invited = $this->getInvited($event);

                return $event->toCalendarEventObject([
                    'is_editable' => $calendarAttributes['permission'] !== 'reader', 'invited' => $invited,
                    'is_repeatable' => ! is_null($event->repeat),
                ]);
            })
            ?->toArray();
    }

    public function getInvited(Model $event): array
    {
        return $event->invitedModels()
            ->map(
                function (Model $inviteable) {
                    return [
                        'id' => $inviteable->id,
                        'label' => $inviteable->getLabel(),
                        'pivot' => $inviteable->pivot,
                    ];
                }
            )
            ->toArray();
    }

    public function getInvites(): array
    {
        return auth()->user()
            ->invites()
            ->with('calendarEvent:id,start,end,title,is_all_day,calendar_id')
            ->get()
            ->toArray();
    }

    public function getCalendars(): array
    {
        return array_merge(
            $this->getMyCalendars()->toArray(),
            $this->getSharedWithMeCalendars()->toArray(),
            $this->getPublicCalendars()->toArray(),
        );
    }

    public function getMyCalendars(): Collection
    {
        return $this->myCalendars = auth()->user()
            ->calendars()
            ->withPivot('permission')
            ->wherePivot('permission', 'owner')
            ->withCount('calendarables')
            ->get()
            ->map(function ($calendar) {
                return $calendar->toCalendarObject(
                    [
                        'permission' => $calendar['pivot']['permission'],
                        'group' => 'my',
                    ]
                );
            });
    }

    public function getSharedWithMeCalendars(): Collection
    {
        return $this->sharedWithMe = auth()->user()
            ->calendars()
            ->withPivot('permission')
            ->wherePivot('permission', '!=', 'owner')
            ->get()
            ->map(function ($calendar) {
                return $calendar->toCalendarObject(
                    [
                        'permission' => $calendar['pivot']['permission'],
                        'resourceEditable' => $calendar['pivot']['permission'] !== 'reader',
                        'group' => 'shared',
                    ]
                );
            });
    }

    public function getPublicCalendars(): Collection
    {
        return config('tall-calendar.models.calendar')::where('is_public', true)
            ->whereNotIn('id', $this->myCalendars->pluck('id'))
            ->whereNotIn('id', $this->sharedWithMe->pluck('id'))
            ->get()
            ->map(function ($calendar) {
                return $calendar->toCalendarObject([
                    'permission' => 'reader',
                    'group' => 'public',
                    'resourceEditable' => false,
                ]);
            });
    }

    public function getViews(): array
    {
        return [
            'dayGridMonth',
        ];
    }

    public function getConfig(): array
    {
        return [
            'locale' => app()->getLocale(),
            'firstDay' => Carbon::getWeekStartsAt(),
            'height' => 'auto',
            'views' => $this->getViews(),
            'headerToolbar' => [
                'end' => 'prev,next today',
                'left' => 'title',
                'center' => 'timeGridDay,timeGridWeek,dayGridMonth',
            ],
            'nowIndicator' => true,
        ];
    }

    public function getCalendarEventsBeingListenedFor(): array
    {
        return array_intersect_key(
            parent::getEventsBeingListenedFor(),
            [
                'select',
                'unselect',
                'dateClick',
                'eventClick',
                'eventMouseEnter',
                'eventMouseLeave',
                'eventDragStart',
                'eventDragStop',
                'eventDrop',
                'eventResizeStart',
                'eventResizeStop',
                'eventResize',
                'eventReceive',
                'eventLeave',
                'eventAdd',
                'eventChange',
                'eventRemove',
                'drop',
                'eventsSet',
            ]
        );
    }

    public function deleteCalendar(int $calendar): bool
    {
        $calendar = config('tall-calendar.models.calendar')::query()
            ->whereKey($calendar)
            ->firstOrFail();

        return $calendar->delete();
    }

    public function saveCalendar(array $attributes): array
    {
        $calendar = config('tall-calendar.models.calendar')::query()->findOrNew($attributes['id'] ?? null);

        $calendar->fromCalendarObject($attributes);

        $calendar->save();

        if (method_exists(auth()->user(), 'calendars')) {
            auth()->user()
                ->calendars()
                ->syncWithoutDetaching($calendar);
        }

        return $calendar->toCalendarObject(['group' => 'my']);
    }

    public function confirmSave(): void
    {
        if (array_key_exists('id', $this->calendarEvent) && $this->calendarEvent['is_repeatable']) {
            $this->confirmOption = 'future';
            $this->dialog()->id('edit-event-dialog')->confirm([
                'icon' => '',
                'accept' => [
                    'label' => __('Ok'),
                    'method' => 'saveEvent',
                    'params' => $this->calendarEvent,
                ],
                'reject' => [
                    'label' => __('Cancel'),
                ],
            ]);
        } else {
            $this->saveEvent($this->calendarEvent);
        }
    }

    public function saveEvent(array $attributes): array|bool
    {
        $validator = Validator::make($attributes, $this->getRules());
        if ($validator->fails()) {
            return false;
        }

        $event = config('tall-calendar.models.calendar_event')::query()
            ->with('invites')
            ->when(
                is_numeric($attributes['id']),
                fn ($query) => $query->whereKey($attributes['id']),
                fn ($query) => $query->where('ulid', $attributes['id'])
            )
            ->firstOrFail();

        if ($attributes['is_repeatable'])

        // If edit option is "selected and future", set repeat_end of original and create new event with given values
        if ($this->confirmOption === 'future') {
            $originalEvent = $event;
            $event = $event->replicate(['ulid']);
            $event->fromCalendarEventObject($attributes);

            $originalEvent->repeat_end = $originalEvent->start->subSecond();
            $originalEvent->save();
        } else {
            $event->fromCalendarEventObject($attributes);
        }

        $event->save();

        $invites = collect($attributes['invited'] ?? [])
            ->map(function ($invite) {
                return [
                    'id' => $invite['id'] ?? null,
                    'is_selected' => $invite['isSelected'] ?? false,
                    'inviteable_id' => $invite['id'],
                    'inviteable_type' => $invite['type'] ?? auth()->user()->getMorphClass(),
                    'email' => $invite['email'] ?? $invite['description'] ?? null,
                    'pivot' => $invite['pivot'] ?? [],
                ];
            });

        $event->invites()
            ->whereNotIn('id', $invites->where('is_selected', false)->pluck('id'))
            ->delete();

        foreach ($invites as $invite) {
            $event->invites()->updateOrCreate(
                [
                    'inviteable_id' => $invite['inviteable_id'],
                    'inviteable_type' => $invite['inviteable_type'],
                ],
                $invite['pivot'],
            );
        }

        $this->dispatch('eventSaved')->self();

        return $event->toCalendarEventObject();
    }

    public function confirmDeletion(): void
    {
        $this->confirmOption = 'this';
        $this->dialog()->id('delete-event-dialog')->confirm([
            'icon' => '',
            'accept' => [
                'label' => __('Ok'),
                'method' => 'deleteEvent',
                'params' => $this->calendarEvent['id'],
            ],
            'reject' => [
                'label' => __('Cancel'),
            ],
        ]);
    }

    public function deleteEvent(int|string $event): bool
    {
        $event = config('tall-calendar.models.calendar_event')::query()
            ->when(
                is_numeric($event),
                fn ($query) => $query->whereKey($event),
                fn ($query) => $query->where('ulid', $event)
            )
            ->firstOrFail();

        if (! $event->repeat) {
            $this->confirmOption = 'all';
        }

        return match ($this->confirmOption) {
            'this' => $event->fill([
                'excluded' => array_merge(
                    $event->excluded ?: [],
                    [Carbon::parse($this->calendarEvent['start'])->format('Y-m-d H:i:s')]
                )
            ])->save(),
            'future' => $event->fill([
                'repeat_end' => Carbon::parse($this->calendarEvent['start'])->subSecond()->format('Y-m-d H:i:s')
            ])->save(),
            default => $event->delete()
        };
    }

    public function inviteStatus(Inviteable $event, string $status, int $calendarId)
    {
        $event->status = $status;
        $event->model_calendar_id = $calendarId;
        $event->save();

        $this->skipRender();
    }

    private function calculateRepeatableEvents($calendar, array $info, Collection $calendarEvents): Collection
    {
        $repeatables = $calendar->calendarEvents()
            ->whereNotNull('repeat')
            ->whereDate('start', '<', $info['end'])
            ->where(fn ($query) => $query->whereDate('repeat_end', '>', $info['start'])
                ->orWhereNull('repeat_end')
            )
            ->get();

        foreach ($repeatables as $repeatable) {
            $recurrences = $repeatable->recurrences;
            $i = 0;
            for ($j = count($repeatValues = explode(',', $repeatable->repeat)); $j > 0; $j--) {
                if ($repeatable->recurrences) {
                    if ($recurrences < 1) {
                        continue;
                    }

                    $datePeriod = new \DatePeriod(
                        Carbon::parse($repeatable->start),
                        \DateInterval::createFromDateString($repeatValues[$i]),
                        ($count = (int) ceil($recurrences / $j)) - (int) ($i === 0), // subtract 1, because start date does not count towards recurrences limit
                        (int) ($i !== 0) // 1 = Exclude start date
                    );

                    $recurrences -= $count;
                } else {
                    $datePeriod = new \DatePeriod(
                        Carbon::parse($repeatable->start),
                        \DateInterval::createFromDateString($repeatValues[$i]),
                        Carbon::parse(is_null($repeatable->repeat_end) ?
                            $info['end'] : min([$repeatable->repeat_end, $info['end']])
                        ),
                        (int) ($i !== 0)
                    );
                }

                // filter dates in between start and end
                $dates = array_filter(
                    iterator_to_array($datePeriod),
                    fn ($item) => ($date = $item->format('Y-m-d H:i:s')) > $info['start'] && $date < $info['end']
                        && ! in_array($date, $repeatable->excluded ?: [])
                );

                $events = array_map(function ($date) use ($repeatable) {
                    $interval = date_diff(Carbon::parse($repeatable->start), Carbon::parse($repeatable->end));

                    return new (config('tall-calendar.models.calendar_event'))(
                        array_merge(
                            $repeatable->toArray(),
                            [
                                'start' => ($start = Carbon::parse($repeatable->start)->setDateFrom($date))
                                    ->format('Y-m-d H:i:s'),
                                'end' => $start->add($interval)->format('Y-m-d H:i:s'),
                            ]
                        )
                    );
                }, $dates);

                foreach ($events as $event) {
                    $calendarEvents->push($event);
                }

                $i++;
            }
        }

        return $calendarEvents;
    }
}
