<?php

namespace TeamNiftyGmbH\Calendar\Models\Pivot;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Inviteable extends MorphPivot
{
    public $table = 'inviteables';

    public function inviteable(): MorphTo
    {
        return $this->morphTo('inviteable');
    }

    public function calendarEvent(): BelongsTo
    {
        return $this->belongsTo(config('tall-calendar.models.calendar_event'));
    }
}
