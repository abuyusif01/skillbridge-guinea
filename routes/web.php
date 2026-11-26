<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WorkerBookingController;
use App\Http\Controllers\WorkerProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/search', SearchController::class)->name('search');
Route::get('/workers/{worker}', WorkerProfileController::class)->name('workers.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->middleware('throttle:10,1')->name('register.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.store');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'active'])
    ->name('dashboard');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');
});

Route::middleware(['auth', 'active', 'role:customer'])->group(function () {
    Route::get('/workers/{worker}/book', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/workers/{worker}/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/sent', [BookingController::class, 'success'])->name('bookings.success');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    Route::get('/bookings/{booking}/review', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/bookings/{booking}/review', [ReviewController::class, 'store'])->name('reviews.store');
});

Route::middleware(['auth', 'active', 'role:worker'])->prefix('worker')->name('worker.')->group(function () {
    Route::get('/profile/edit', [WorkerProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [WorkerProfileController::class, 'update'])->name('profile.update');

    Route::patch('/bookings/{booking}/accept', [WorkerBookingController::class, 'accept'])->name('bookings.accept');
    Route::patch('/bookings/{booking}/decline', [WorkerBookingController::class, 'decline'])->name('bookings.decline');
    Route::patch('/bookings/{booking}/complete', [WorkerBookingController::class, 'complete'])->name('bookings.complete');
});

Route::middleware(['auth', 'active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::patch('/users/{user}/status', [AdminUserController::class, 'update'])->name('users.status');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
});
