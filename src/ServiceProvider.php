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
        // Storyblok Clients
        $this->app->bind(Client::class, function () {
            return new Client(env('STORYBLOK_PREVIEW_KEY'));
        });

        $this->app->bind(ManagementClient::class, function () {
            return new ManagementClient(env('STORYBLOK_MANAGEMENT_KEY'));
        });

        $this->publishes([
            __DIR__ . '/../config/laravel-storyblok.php' => config_path('laravel-storyblok.php'),
        ]);
    }
}
