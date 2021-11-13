<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RemindersController;
use Illuminate\Support\Facades\Auth;

Route::middleware('verified')->group(function() {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/reminders', [RemindersController::class, 'index'])->name('reminders.index');
    Route::post('/reminders', [RemindersController::class, 'store'])->name('reminders.store');
    Route::patch('/reminders/{reminder}', [RemindersController::class, 'update'])->name('reminders.update');
    Route::delete('/reminders/{reminder}', [RemindersController::class, 'destroy'])->name('reminders.destroy');
});

Auth::routes(['verify' => true]);
