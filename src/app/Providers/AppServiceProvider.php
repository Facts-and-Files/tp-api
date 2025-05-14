<?php

namespace App\Providers;

use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;
use App\Models\Story;
use App\Observers\StoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }


        $this->app->singleton(Client::class, function () {
            $adapter = new Curl(); // Default Curl adapter
            $dispatcher = new EventDispatcher();
            return new Client($adapter, $dispatcher, [
                'endpoint' => [
                    'localhost' => [
                        'host' => config('services.solr.host'),
                        'port' => config('services.solr.port'),
                        'path' => config('services.solr.path'),
                        'core' => config('services.solr.core'),
                    ]
                ]
            ]);
        });
    }

    public function boot(): void
    {
        Story::observe(StoryObserver::class);
    }
}
