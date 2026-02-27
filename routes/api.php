<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TimeSlotController;
use App\Http\Controllers\Api\WaitlistController;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{event}', [EventController::class, 'update']);
    Route::delete('/events{event}', [EventController::class, 'destroy']);
    Route::post('/events/{event}/slots', [TimeSlotController::class, 'store']);
    Route::put('/slots/{slot}', [TimeSlotController::class, 'update']);
    Route::delete('/slots/{slot}', [TimeSlotController::class, 'destroy']);
    Route::post('/book', [BookingController::class, 'book']);
    Route::post('/cancel/{booking}', [BookingController::class, 'cancel']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::post('/waitlist', [WaitlistController::class, 'join']);
    Route::post('/waitlist/cancel/{id}', [WaitlistController::class, 'cancel']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);
