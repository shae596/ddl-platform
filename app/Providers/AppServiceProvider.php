<?php

namespace App\Providers;

use App\Models\Notification;
use App\Support\RailwayAppConfig;
use App\Support\RailwayDatabaseConfig;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        RailwayDatabaseConfig::apply();
    }

    public function boot(): void
    {
        RailwayAppConfig::apply();

        View::composer(['layouts.agent', 'layouts.secretariat', 'layouts.di', 'layouts.developpeur'], function ($view) {
            if (auth()->check()) {
                $view->with(
                    'notificationsNonLues',
                    Notification::query()
                        ->where('user_id', auth()->id())
                        ->where('lue', false)
                        ->count()
                );
            }
        });
    }
}

