<?php

namespace App;

use App\Models\Reminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GenerateRepeatingReminders
{
    public function __invoke()
    {
        Reminder::where('due_at', Carbon::now()->seconds(0))
            ->whereNotNull('repeats')->each(function($reminder) {

            $repeat = explode(' ', $reminder->repeats);

            $reminder->user->reminders()->save(
                new Reminder([
                    'title' => $reminder->title,
                    'due_at' => Carbon::parse($reminder->due_at)->add($repeat[0], $repeat[1]),
                    'repeats' => $reminder->repeats,
                ])
            );
        });
    }
}
