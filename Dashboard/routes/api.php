<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

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
