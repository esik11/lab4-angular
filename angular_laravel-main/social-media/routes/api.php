<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;

Route::middleware('auth:sanctum')->group(function () {
    // Posts Routes
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::put('/posts/{postId}', [PostController::class, 'update']);
    Route::post('/posts/{post}/like', [PostController::class, 'likePost']);
    Route::post('/posts/{post}/unlike', [PostController::class, 'unlikePost']);
    Route::post('/posts/{post}/comments', [PostController::class, 'addComment']);
    Route::put('posts/{post}/comments/{comment}', [PostController::class, 'editComment']);
    Route::delete('posts/{post}/comments/{comment}', [PostController::class, 'deleteComment']);
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile', [ProfileController::class, 'update']);

    // User Route
    Route::get('/user', [UserController::class, 'getUser']);

    // Notifications
    Route::get('/notifications/unread', [NotificationController::class, 'fetchUnreadNotifications']);
    Route::get('/notifications/all', [NotificationController::class, 'fetchAllNotifications']);
    Route::post('/notifications/{postId}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification']);
});
