<?php

namespace App\View\Composers;

use App\Support\Breadcrumbs;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class BreadcrumbComposer
{
    public function compose(View $view): void
    {
        if ($view->offsetExists('breadcrumbs') || ($view->offsetExists('hideBreadcrumb') && $view->getData()['hideBreadcrumb'])) {
            return;
        }

        $items = Breadcrumbs::resolve(Route::currentRouteName(), $view->getData());

        if ($items) {
            $view->with('breadcrumbs', $items);
        }
    }
}
