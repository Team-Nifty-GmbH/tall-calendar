<?php

namespace TeamNiftyGmbH\Calendar\Models\Pivot;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class Calendarable extends MorphPivot
{
    protected $table = 'calendarables';
}
