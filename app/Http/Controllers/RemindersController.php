<?php

namespace App\Http\Controllers;

use DB;
use App\Reminder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RemindersController extends Controller
{
    public function index()
    {
        return view('reminders.index', [
            'reminders' => request()->user()->reminders()->where('due_at', '>=', Carbon::now())->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
        ]);

        request()->user()->reminders()->save(
            new Reminder([
                'title' => $request->title,
                'due_at' => Carbon::parse($request->date . " " . $request->time)->seconds(0),
            ])
        );

        return redirect()->back();
    }

    public function destroy(Reminder $reminder)
    {
        if($reminder->user_id != request()->user()->id) {
            return 403;
        }

        $reminder->delete();

        return redirect()->back();
    }
}
