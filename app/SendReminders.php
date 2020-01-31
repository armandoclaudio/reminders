<?php

namespace App;

use App\User;
use App\Reminder;
use Carbon\Carbon;
use App\Notifications\ReminderNotification;

class SendReminders {

    public function __invoke() {
        User::whereHas('reminders', function($query) {
            $query->where('due_at', Carbon::now()->seconds(0));
        })->groupBy('id')->get()->each(function($user) {
            $reminders = $user->reminders()->where('due_at', Carbon::now()->seconds(0));
            $user->notify(new ReminderNotification($reminders->get()));
        });
    }
}
