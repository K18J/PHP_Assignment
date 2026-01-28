<?php

namespace App\Providers;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\DashboardRepository;
use App\Services\Contracts\DashboardServiceInterface;
use App\Services\DashboardService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );

        $this->app->bind(
            DashboardServiceInterface::class,
            DashboardService::class
        );
    }

    public function boot() { }
}