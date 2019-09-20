<?php

namespace App;

use App\Reminder;
use Carbon\Carbon;
use App\Notifications\ReminderNotification;

class SendReminders {
    public function __invoke() {
        Reminder::where('due_at', Carbon::now()->seconds(0))->get()->each(function($reminder) {
            $reminder->user->notify(new ReminderNotification($reminder));
        });
    }
}