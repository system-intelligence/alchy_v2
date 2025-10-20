<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebPushController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->isDeveloper()) {
        return view('dashboard-developer');
    } elseif ($user->isSystemAdmin()) {
        return redirect()->route('admin.dashboard');
    } else {
        return view('dashboard-user');
    }
})->middleware(['auth'])->name('dashboard');

Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('admin.dashboard');

Route::get('/masterlist', function () {
    return view('masterlist');
})->middleware(['auth'])->name('masterlist');

Route::get('/history', function () {
    return view('history');
})->middleware(['auth'])->name('history');

Route::get('/expenses', function () {
    if (auth()->user()->isUser()) {
        abort(403, 'Access denied');
    }
    return view('expenses');
})->middleware(['auth'])->name('expenses');

Route::get('/developer/user-management', function () {
    return view('developer.user-management');
})->middleware(['auth'])->name('developer.user-management');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Web Push subscription endpoints
    Route::post('/push/subscribe', [WebPushController::class, 'subscribe'])->name('push.subscribe');
    Route::delete('/push/unsubscribe', [WebPushController::class, 'unsubscribe'])->name('push.unsubscribe');
});

require __DIR__.'/auth.php';
