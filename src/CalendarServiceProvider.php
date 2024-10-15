<?php

namespace TeamNiftyGmbH\Calendar;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use TeamNiftyGmbH\Calendar\Livewire\CalendarComponent;
use TeamNiftyGmbH\Calendar\Livewire\CalendarOverview;

class CalendarServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->mergeConfigFrom(
            __DIR__ . '/../config/tall-calendar.php',
            'tall-calendar'
        );

        Livewire::component('calendar-component', CalendarComponent::class);
        Livewire::component('calendar-overview', CalendarOverview::class);
    }

    public function boot(): void
    {
        $this->offerPublishing();

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tall-calendar');
    }

    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tall-calendar.php' => config_path('tall-calendar.php'),
            ], 'tall-calendar-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/tall-calendar'),
            ], 'tall-calendar-views');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'tall-calendar-migrations');
        }
    }
}
