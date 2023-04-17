<?php

namespace TeamNiftyGmbH\Calendar\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use TeamNiftyGmbH\Calendar\Models\CalendarEvent;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\FluxErp\Models\CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CalendarEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $startsAt = Carbon::createFromTimestamp(
            $this->faker->dateTimeBetween('-90 days', '+90 days')->getTimeStamp()
        );
        $endsAt = $startsAt->clone();
        $endsAt = $this->faker->boolean(15) ? $endsAt->addDays(rand(0, 5)) : null;

        return [
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->text(),
            'start' => $startsAt,
            'end' => $endsAt,
            'is_all_day' => $this->faker->boolean(),
        ];
    }
}
