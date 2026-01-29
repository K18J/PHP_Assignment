<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;

Route::prefix('dashboard')->group(function () {
    // Get dashboard statistics
    Route::get('/stats', [DashboardController::class, 'getStats'])->name('api.dashboard.stats');
    
    // Get table data
    Route::get('/table-data', [DashboardController::class, 'getTableData'])->name('api.dashboard.table-data');
    
    // Get chart data
    Route::get('/chart-data', [DashboardController::class, 'getChartData'])->name('api.dashboard.chart-data');
    
    // Delete table row
    Route::delete('/table-data/{id}', [DashboardController::class, 'deleteTableRow'])->name('api.dashboard.delete-row');
});

Route::prefix('assets')->group(function () {
    Route::get('/', [AssetController::class, 'list'])->name('api.assets.list');
    Route::get('/categories', [AssetController::class, 'categories'])->name('api.assets.categories');
    Route::get('/{id}', [AssetController::class, 'show'])->name('api.assets.show');
    Route::post('/', [AssetController::class, 'store'])->name('api.assets.store');
    Route::put('/{id}', [AssetController::class, 'update'])->name('api.assets.update');
    Route::delete('/{id}', [AssetController::class, 'destroy'])->name('api.assets.destroy');
});
