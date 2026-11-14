<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScoreController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::get('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::post('/user', [UserController::class, 'store'])->name('user.store');
    Route::get('/user/{user}', [UserController::class, 'show'])->name('user.show')->withTrashed();
    Route::get('/user/{user}/edit', [UserController::class, 'edit'])->name('user.edit');
    Route::patch('/user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');
    Route::patch('/user/{user}/restore', [UserController::class, 'restore'])->name("user.restore")->withTrashed();
    Route::delete('/user/{user}/force-delete', [UserController::class, 'forceDelete'])->name("user.forcedelete")->withTrashed();
    
    Route::get('/score', [ScoreController::class, 'index'])->name("score.index");
    Route::get('/score/{score}', [ScoreController::class, 'show'])->name('score.show')->withTrashed();
    Route::delete('/score/{score}', [ScoreController::class, 'destroy'])->name('score.destroy');
    Route::patch('/score/{score}/restore', [ScoreController::class, 'restore'])->name("score.restore")->withTrashed();
    Route::delete('/score/{score}/force-delete', [ScoreController::class, 'forceDelete'])->name("score.forcedelete")->withTrashed();
});

require __DIR__.'/auth.php';
