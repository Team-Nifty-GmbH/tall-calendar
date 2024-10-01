<?php

namespace TeamNiftyGmbH\Calendar\Models;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use TeamNiftyGmbH\Calendar\Models\Pivot\Calendarable;
use TeamNiftyGmbH\Calendar\Support\CalendarCollection;
use TeamNiftyGmbH\Calendar\Traits\HasPackageFactory;

class Calendar extends Model
{
    use BroadcastsEvents, HasPackageFactory;

    protected $guarded = [
        'id',
    ];

    protected static function booted(): void
    {
        static::deleting(function ($calendar) {
            $calendar->calendarEvents()->delete();
        });
    }

    protected function casts(): array
    {
        return [
            'custom_properties' => 'array',
            'has_notifications' => 'boolean',
            'has_repeatable_events' => 'boolean',
            'is_editable' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function calendarables(): HasMany
    {
        return $this->hasMany(Calendarable::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(config('tall-calendar.models.calendar_event'));
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function newCollection(array $models = []): Collection
    {
        return app(CalendarCollection::class, ['items' => $models]);
    }

    public function toCalendarObject(array $attributes = []): array
    {
        return array_merge(
            [
                'id' => $this->id,
                'parentId' => $this->parent_id,
                'modelType' => $this->model_type,
                'name' => $this->name,
                'color' => $this->color,
                'customProperties' => $this->custom_properties ?? [],
                'resourceEditable' => $this->is_editable ?? true,
                'hasRepeatableEvents' => $this->has_repeatable_events ?? true,
                'isPublic' => $this->is_public ?? false,
                'isShared' => $this->calendarables_count > 1,
                'children' => $this->id ? static::query()
                    ->where('parent_id', $this->id)
                    ->count('id') : 0,
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
