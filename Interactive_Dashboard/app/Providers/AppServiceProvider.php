<?php

namespace App\Providers;

use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use App\Repositories\AssetRepository;
use App\Repositories\DashboardRepository;
use App\Services\Contracts\AssetServiceInterface;
use App\Services\Contracts\DashboardServiceInterface;
use App\Services\AssetService;
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

        $this->app->bind(
            AssetRepositoryInterface::class,
            AssetRepository::class
        );

        $this->app->bind(
            AssetServiceInterface::class,
            AssetService::class
        );
    }

    public function boot() {}
}