<?php

namespace TeamNiftyGmbH\Calendar\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;

trait HasPackageFactory
{
    use HasFactory;

    protected static function newFactory()
    {
        return app('TeamNiftyGmbH\Calendar\Database\Factories\\'
            .class_basename(static::class)
            .'Factory'
        );
    }
}
