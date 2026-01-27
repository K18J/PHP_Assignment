<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\CommentController;
use Modules\Cms\Http\Controllers\PageController;
use Modules\Cms\Http\Middleware\EnsureCommentAdmin;
use Modules\Cms\Http\Middleware\SpamDetectionMiddleware;

Route::prefix('cms')->name('cms.')->group(function (): void {
    // Pages CRUD
    Route::get('pages', [PageController::class, 'index']);
    Route::post('pages', [PageController::class, 'store']);
    Route::get('pages/{page}', [PageController::class, 'show']);
    Route::put('pages/{page}', [PageController::class, 'update']);
    Route::delete('pages/{page}', [PageController::class, 'destroy']);

    // Comments - all comments endpoint
    Route::get('comments', [CommentController::class, 'getAll']);

    Route::get('pages/{page}/comments', [CommentController::class, 'index']);
    Route::post('pages/{page}/comments', [CommentController::class, 'store'])
        ->middleware(SpamDetectionMiddleware::class);

    Route::put('comments/{comment}', [CommentController::class, 'update']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    Route::post('comments/{comment}/approve', [CommentController::class, 'approve'])
        ->middleware(EnsureCommentAdmin::class);
    Route::post('comments/{comment}/reject', [CommentController::class, 'reject'])
        ->middleware(EnsureCommentAdmin::class);
});

