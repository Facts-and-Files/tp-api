<?php

namespace App\Providers;

use App\Models\Item;
use App\Models\Story;
use App\Observers\ItemObserver;
use App\Observers\StoryObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;
use Solarium\Client;
use Solarium\Core\Client\Adapter\Curl;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local')) {
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
                    ],
                ],
            ]);
        });
    }

    public function boot(): void
    {
        Story::observe(StoryObserver::class);

        Item::observe(ItemObserver::class);

        if ($this->app->environment('local')) {
            Model::preventLazyLoading();
        }
    }
}
