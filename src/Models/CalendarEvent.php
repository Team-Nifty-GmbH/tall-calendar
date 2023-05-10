<?php

namespace TeamNiftyGmbH\Calendar\Models;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use TeamNiftyGmbH\Calendar\Traits\HasPackageFactory;

class CalendarEvent extends Model
{
    use BroadcastsEvents, HasPackageFactory, HasUlids;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'is_all_day' => 'boolean',
        'extended_props' => 'array',
    ];

    public function calendar(): BelongsTo
    {
        return $this->belongsTo(config('tall-calendar.models.calendar'));
    }

    public function invites(): HasMany
    {
        return $this->hasMany(config('tall-calendar.models.invitable'));
    }

    public function uniqueIds()
    {
        return ['ulid'];
    }

    public function toCalendarEventObject()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->start,
            'end' => $this->end,
            'allDay' => $this->is_all_day,
            'calendar_id' => $this->calendar_id,
            'extendedProps' => $this->extended_props,
        ];
    }
}
