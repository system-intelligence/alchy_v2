<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceiptVerificationController;
use App\Http\Controllers\WebPushController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Broadcasting authentication routes for Pusher private channels
Broadcast::routes(['middleware' => ['auth']]);

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
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

Route::get('/approvals', function () {
    $user = auth()->user();
    if (!$user->isSystemAdmin() && !$user->isDeveloper()) {
        abort(403, 'Access denied');
    }
    return view('approvals');
})->middleware(['auth'])->name('approvals');

Route::get('/expenses', function () {
    // Allow all authenticated users - regular users can request releases (approval needed)
    // Only system admins/developers can directly release
    return view('expenses');
})->middleware(['auth'])->name('expenses');

Route::get('/tools', function () {
    return view('tools');
})->middleware(['auth'])->name('tools');


Route::get('/developer/user-management', function () {
    return view('developer.user-management');
})->middleware(['auth'])->name('developer.user-management');

// Public routes for receipt verification (no auth required)
Route::get('/verify-receipt/{hash}', [ReceiptVerificationController::class, 'verify'])->name('verify-receipt');
Route::get('/receipt-scanner', [ReceiptVerificationController::class, 'scanner'])->name('receipt-scanner');

Route::middleware(['auth', 'throttle:60,1', 'secure.headers'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Web Push subscription endpoints
    Route::post('/push/subscribe', [WebPushController::class, 'subscribe'])->name('push.subscribe');
    Route::delete('/push/unsubscribe', [WebPushController::class, 'unsubscribe'])->name('push.unsubscribe');
});

require __DIR__.'/auth.php';
