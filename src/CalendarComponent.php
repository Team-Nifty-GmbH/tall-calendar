<?php

namespace TeamNiftyGmbH\Calendar;

use Carbon\Carbon;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Livewire\Component;
use TeamNiftyGmbH\Calendar\Models\Calendar;
use TeamNiftyGmbH\Calendar\Models\CalendarEvent;
use WireUi\Traits\Actions;

class CalendarComponent extends Component
{
    use Actions;

    public bool $showCalendars = true;

    public bool $showInvites = true;

    private Collection $sharedWithMe;
    private Collection $myCalendars;

    public function render(): Factory|Application|View
    {
        return view('tall-calendar::livewire.calendar');
    }

    public function getEvents(array $info, Calendar $calendar): array
    {
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
            ->get()
            ->map(function (CalendarEvent $event) {
                return $event->toCalendarEventObject();
            })
            ?->toArray();
    }

    public function getInvites()
    {
        return auth()->user()
            ->invites()
            ->wherePivot('status')
            ->withPivot()
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
            ->wherePivot('permission', 'owner')
            ->withCount('calendarables')
            ->get()
            ->map(function (Calendar $calendar) {
                $calendar = $calendar->toCalendarObject();
                $calendar['group'] = 'my';

                return $calendar;
            });
    }

    public function getSharedWithMeCalendars(): Collection
    {
        return $this->sharedWithMe = auth()->user()
            ->calendars()
            ->wherePivot('permission', '!=', 'owner')
            ->get()
            ->map(function (Calendar $calendar) {
                $calendar = $calendar->toCalendarObject();
                $calendar['group'] = 'shared';

                return $calendar;
            });
    }

    public function getPublicCalendars(): Collection
    {
        return Calendar::where('is_public', true)
            ->whereNotIn('id', $this->myCalendars->pluck('id'))
            ->whereNotIn('id', $this->sharedWithMe->pluck('id'))
            ->get()
            ->map(function (Calendar $calendar) {
                $calendar = $calendar->toCalendarObject();
                $calendar['group'] = 'public';

                return $calendar;
            });
    }

    public function getViews(): array
    {
        return [
            'dayGridMonth'
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

    public function deleteCalendar(Calendar $calendar): bool
    {
        return $calendar->delete();
    }

    public function saveCalendar(array $attributes): array
    {
        $calendar = Calendar::findOrNew($attributes['id'] ?? null);
        $calendar->fill($attributes);

        $calendar->save();
        auth()
            ->user()
            ->calendars()
            ->attach($calendar);

        return $calendar->toCalendarObject(['group' => 'my']);
    }
}
