<?php

namespace TeamNiftyGmbH\Calendar\Models;

use FluxErp\Models\User;
use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
        return $this->hasMany(config('tall-calendar.models.inviteable'));
    }

    public function invited(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(User::class, 'inviteable');
    }

    public function invitedModels(): Collection
    {
        $types = $this->invites()->distinct('inviteable_type')->pluck('inviteable_type')->toArray();

        $invitedModels = collect();
        foreach ($types as $type) {
            $invitedModels = $invitedModels->merge($this->morphedByMany($type, 'inviteable')->withPivot('status')->get());
        }

        return $invitedModels;
    }

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function toCalendarEventObject(array $attributes = []): array
    {
        return array_merge(
            [
                'id' => $this->id,
                'title' => $this->title,
                'start' => $this->start->format('Y-m-d\TH:i:s.u'),
                'end' => $this->end?->format('Y-m-d\TH:i:s.u'),
                'allDay' => $this->is_all_day,
                'calendar_id' => $this->calendar_id,
                'editable' => ! $this->calendar->is_public && ! $this->is_invited,
                'is_editable' => ! $this->calendar->is_public && ! $this->is_invited,
                'is_invited' => $this->is_invited,
                'is_public' => $this->calendar->is_public,
                'status' => $this->status ?: 'busy',
                'invited' => $this->invited->toArray(),
                'description' => $this->description,
                'extendedProps' => $this->extended_props,
            ],
            $attributes
        );
    }

    public function fromCalendarEventObject(array $calendarEvent): static
    {
        $mappedArray = [];

        foreach ($calendarEvent as $key => $value) {
            $mappedArray[Str::snake($key)] = $value;
        }

        $mappedArray['is_all_day'] = $calendarEvent['allDay'] ?? false;

        foreach ($mappedArray['extended_props'] ?? [] as $key => $value) {
            $mappedArray[Str::snake($key)] = $mappedArray[Str::snake($key)] ?? $value;
        }

        $this->fill($mappedArray);

        return $this;
    }
}
