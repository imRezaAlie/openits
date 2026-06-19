<?php

namespace App\Providers;

use App\Events\LdapSyncCompleted;
use App\Listeners\LogLdapSyncCompleted;
use App\Services\LdapService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class LdapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(LdapService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(LdapSyncCompleted::class, LogLdapSyncCompleted::class);
    }
}
