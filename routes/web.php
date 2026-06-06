<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransactionController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('admin')->name('admin.')->middleware('verified')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/staff', [AdminDashboardController::class, 'staff'])->name('staff.index');
        Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('transactions.index');
        Route::patch('/transactions/{team}/accept', [AdminDashboardController::class, 'acceptTransaction'])->name('transactions.accept');
        Route::patch('/transactions/{team}/reject', [AdminDashboardController::class, 'rejectTransaction'])->name('transactions.reject');
        Route::get('/files-participants', [AdminDashboardController::class, 'filesParticipants'])->name('files-participants.index');
        Route::get('/timelines', [AdminDashboardController::class, 'timelines'])->name('timelines.index');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::post('/transaction/{teamId}/verify', [TransactionController::class, 'verify']);
    Route::get('/transaction/recap', [TransactionController::class, 'getRecap']);
});

require __DIR__.'/auth.php';
