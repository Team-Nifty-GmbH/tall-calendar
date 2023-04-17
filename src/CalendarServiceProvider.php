<?php

namespace TeamNiftyGmbH\Calendar;

use Illuminate\Support\ServiceProvider;

class CalendarServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->mergeConfigFrom(
            __DIR__.'/../config/tall-calendar.php',
            'tall-calendar'
        );
    }

    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        $this->offerPublishing();

//        $this->commands([
//            MakeDataTableCommand::class,
//            ModelInfoCache::class,
//            ModelInfoCacheReset::class,
//        ]);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tall-calendar');

//        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    protected function offerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tall-calendar.php' => config_path('tall-calendar.php'),
            ], 'tall-calendar-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/tall-calendar'),
            ], 'tall-calendar-views');

            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/tall-calendar'),
            ], 'tall-calendar-lang');
        }
    }
}
