<?php

namespace TeamNiftyGmbH\Calendar\Support;

use Illuminate\Database\Eloquent\Collection;

class CalendarCollection extends Collection
{
    public function toFlatTree(): \Illuminate\Support\Collection
    {
        $tree = [];

        foreach (collect($this->items)->sortBy('name')->sortBy('parent_id') as $item) {
            $tree[$item->parent_id ?? $item->id][] = $item;
        }

        return collect($tree)->flatten(1);
    }
}
