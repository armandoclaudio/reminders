<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('verified')->group(function() {
    Route::get('/', 'RemindersController@index')->name('reminders.index');
    Route::post('/', 'RemindersController@store')->name('reminders.store');
    Route::patch('/{reminder}', 'RemindersController@update')->name('reminders.update');
    Route::delete('/{reminder}', 'RemindersController@destroy')->name('reminders.destroy');
});
Auth::routes(['verify' => true]);
