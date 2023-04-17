<?php

namespace TeamNiftyGmbH\Calendar\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasCalendars
{
    public function calendars(): MorphToMany
    {
        return $this->morphToMany(config('tall-calendar.models.calendar'), 'calendarable');
    }

    public function invites(): MorphMany
    {
        return $this->morphMany(config('tall-calendar.models.calendar_event'), 'calendarable');
    }
}
