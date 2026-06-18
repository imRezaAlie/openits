<?php

namespace App\Providers;

use App\Models\Vendor;
use App\View\Composers\BreadcrumbComposer;
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

        View::composer('master', BreadcrumbComposer::class);
    }
}
