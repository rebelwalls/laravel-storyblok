<?php

namespace RebelWalls\LaravelStoryblok\Client;

/**
 * Class ServiceProvider
 *
 * @package RebelWalls\LaravelStoryblok\Client
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-storyblok.php', 'laravel-storyblok'
        );
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-storyblok.php' => config_path('laravel-storyblok.php'),
        ]);
    }
}
