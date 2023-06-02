<?php

namespace TeamNiftyGmbH\Calendar\Models;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use TeamNiftyGmbH\Calendar\Models\Pivot\Calendarable;
use TeamNiftyGmbH\Calendar\Traits\HasPackageFactory;

class Calendar extends Model
{
    use BroadcastsEvents, HasPackageFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'has_notifications' => 'boolean',
        'is_editable' => 'boolean',
        'is_public' => 'boolean',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::deleting(function ($calendar) {
            $calendar->calendarEvents()->delete();
        });
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(config('tall-calendar.models.calendar_event'));
    }

    public function invitesCalendarEvents()
    {
        return $this->hasManyThrough(
            config('tall-calendar.models.calendar_event'),
            config('tall-calendar.models.inviteable'),
            'model_calendar_id',
            'id',
            'id',
            'calendar_event_id');
    }

    public function calendarables(): HasMany
    {
        return $this->hasMany(Calendarable::class);
    }

    public function toCalendarObject(array $attributes = []): array
    {
        return array_merge(
            [
                'id' => $this->id,
                'name' => $this->name,
                'color' => $this->color,
                'resourceEditable' => $this->is_editable,
                'isPublic' => $this->is_public,
                'isShared' => $this->calendarables_count > 1,
            ],
            $attributes
        );
    }

    public function fromCalendarObject(array $calendar): static
    {
        $mappedArray = [];

        foreach ($calendar as $key => $value) {
            $mappedArray[Str::snake($key)] = $value;
        }

        $this->fill($mappedArray);

        return $this;
    }
}
