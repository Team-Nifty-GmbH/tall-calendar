<?php

namespace TeamNiftyGmbH\Calendar\Database\Seeders;

use Illuminate\Database\Seeder;
use TeamNiftyGmbH\Calendar\Models\Calendar;
use TeamNiftyGmbH\Calendar\Models\CalendarEvent;

class CalendarEventTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Calendar::all() as $calendar) {
            $calendar->calendarEvents()->saveMany(CalendarEvent::factory()->count(10)->make());
        }
    }
}
