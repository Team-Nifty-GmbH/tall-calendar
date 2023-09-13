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

    private Collection $sharedWithMe;

    private Collection $myCalendars;

    public function mount()
    {
        $this->calendarEvent = ['start' => now(), 'end' => now()];
    }

    public function getRules()
    {
        return [
            'title' => 'required|string',
            'start' => 'required|date',
            'end' => 'required|date',
            'description' => 'nullable|string',
        ];
    }

    public function render(): Factory|Application|View
    {
        return view('tall-calendar::livewire.calendar');
    }

    public function getEvents(array $info, array $calendarAttributes): array
    {
        $calendar = config('tall-calendar.models.calendar')::query()->find($calendarAttributes['id']);

        return $calendar->calendarEvents()
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
            )
            ->map(function ($event) use ($calendarAttributes) {
                $invited = $this->getInvited($event);

                return $event->toCalendarEventObject(['is_editable' => $calendarAttributes['permission'] !== 'reader', 'invited' => $invited]);
            })
            ?->toArray();
    }

    public function getInvited(Model $event)
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

    public function getInvites()
    {
        return auth()->user()
            ->invites()
            ->with('calendarEvent:id,start,end,title,is_all_day,calendar_id')
            ->get()
            ->toArray();
    }

    public function getCalendars()
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
            method_exists(parent::class, 'getEventsBeingListenedFor')
                ? parent::getEventsBeingListenedFor()
                : array_keys(parent::getListeners()),
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

    public function saveEvent(array $attributes): array|bool
    {
        $this->skipRender();
        $validator = Validator::make($attributes, $this->getRules());
        if ($validator->fails()) {
            return false;
        }

        $event = config('tall-calendar.models.calendar_event')::query()
            ->with('invites')
            ->findOrNew($attributes['id'] ?? null);

        $event->fromCalendarEventObject($attributes);
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

        return $event->toCalendarEventObject();
    }

    public function deleteEvent(int $event): bool
    {
        $event = config('tall-calendar.models.calendar_event')::query()
            ->whereKey($event)
            ->firstOrFail();

        return $event->delete();
    }

    public function inviteStatus(Inviteable $event, string $status, int $calendarId)
    {
        $event->status = $status;
        $event->model_calendar_id = $calendarId;
        $event->save();

        $this->skipRender();
    }
}
