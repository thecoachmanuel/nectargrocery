<?php

namespace App\Providers;

use App\Enums\OrderStatus;
use App\Enums\Roles;
use App\Models\GeneraleSetting;
use App\Models\Order;
use App\Models\User;
use App\Repositories\LanguageRepository;
use App\Repositories\ThemeColorRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        Paginator::useBootstrapFive();

        if(request()->ip() != '127.0.0.1'){
            Schema::defaultStringLength(191);
            if (!file_exists(base_path('storage/installed')) && !request()->is('install') && !request()->is('install/*')) {
                header("Location: install");
                exit;
            }
        }

        // Share global variables needed by admin/shop layouts
        View::composer(['layouts.app', 'layouts.*', 'admin.*', 'shop.*'], function ($view) {
            try {
                $languages = Cache::rememberForever('all_languages', function () {
                    return LanguageRepository::query()->active()->get();
                });
            } catch (\Throwable $e) {
                $languages = collect();
            }

            try {
                $generaleSetting = generaleSetting('setting');
            } catch (\Throwable $e) {
                $generaleSetting = null;
            }

            $view->with('languages', $languages);

            try {
                $businessModel = ($generaleSetting?->shop_type == 'single') ? 'single' : 'multi';
            } catch (\Throwable $e) {
                $businessModel = 'multi';
            }
            $view->with('businessModel', $businessModel);

            $view->with('seederRun', false);
            $view->with('storageLink', false);

            // Only set generaleSetting if the view doesn't already have it
            if (!isset($view->getData()['generaleSetting'])) {
                $view->with('generaleSetting', $generaleSetting);
            }
        });
    }
}

