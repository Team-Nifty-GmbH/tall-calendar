<?php

namespace TeamNiftyGmbH\Calendar\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

class CalendarOverview extends Component
{
    public bool $showCalendars = true;

    public bool $showInvites = true;

    public array $selectedCalendar;

    public array $parentCalendars = [];

    public function mount(): void
    {
        $this->selectedCalendar = (new (config('tall-calendar.models.calendar'))())
            ->toCalendarObject();
    }

    public function render(): Factory|Application|View
    {
        return view('tall-calendar::livewire.calendar.calendar-overview');
    }

    public function editCalendar(?array $calendar = null): void
    {
        if (is_null($calendar)) {
            $calendar = (new (config('tall-calendar.models.calendar'))())
                ->toCalendarObject();

            $calendar['color'] = '#' . dechex(rand(0x000000, 0xFFFFFF));
        }

        $this->selectedCalendar = $calendar;

        $this->parentCalendars = app(config('tall-calendar.models.calendar'))->query()
            ->whereKeyNot(data_get($calendar, 'id'))
            ->whereNull('parent_id')
            ->when(
                data_get($calendar, 'id'),
                fn ($query) => $query->where('model_type', data_get($calendar, 'modelType'))
            )
            ->get(['id', 'name', 'description'])
            ->toArray();

        $this->js(
            <<<'JS'
                $openModal('calendar-modal');
            JS
        );
    }

    public function saveCalendar(): array|false
    {
        $calendar = config('tall-calendar.models.calendar')::query()
            ->whereKey(data_get($this->selectedCalendar, 'id'))
            ->firstOrNew();

        $calendar->fromCalendarObject($this->selectedCalendar);
        $calendar->save();

        if (method_exists(auth()->user(), 'calendars')) {
            auth()->user()
                ->calendars()
                ->syncWithoutDetaching($calendar);
        }

        return $calendar->toCalendarObject(['group' => 'my']);
    }

    public function deleteCalendar(array $attributes): bool
    {
        $calendar = config('tall-calendar.models.calendar')::query()
            ->whereKey($attributes['id'] ?? null)
            ->firstOrFail();

        return $calendar->delete();
    }
}
