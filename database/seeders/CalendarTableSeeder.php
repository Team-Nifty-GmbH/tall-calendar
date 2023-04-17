<?php

namespace TeamNiftyGmbH\Calendar\Database\Seeders;

use Illuminate\Database\Seeder;
use TeamNiftyGmbH\Calendar\Models\Calendar;
use TeamNiftyGmbH\Calendar\Traits\HasCalendars;

class CalendarTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Calendar::factory()->count(5)->create([
            'is_public' => true,
        ]);

        $calendarables = collect(config('auth.providers'))
            ->pluck('model')
            ->filter(fn($model) => class_uses_recursive($model)[HasCalendars::class] ?? false);

        foreach ($calendarables as $calendarable) {
            foreach ($calendarable::all() as $model) {
                $model->calendars()->saveMany(Calendar::factory()->count(3)->make());
            }
        }
    }
}
