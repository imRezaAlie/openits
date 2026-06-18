<?php

namespace App\Providers;

use App\Events\ApiDocumentationUpdated;
use App\Listeners\SyncC4FromApiDocumentation;
use App\Models\Vendor;
use App\View\Composers\BreadcrumbComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Route::bind('supplier', fn (string $value) => Vendor::findOrFail($value));

        Event::listen(ApiDocumentationUpdated::class, SyncC4FromApiDocumentation::class);

        View::composer('master', BreadcrumbComposer::class);
    }
}
