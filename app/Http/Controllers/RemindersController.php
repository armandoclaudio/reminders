<?php

namespace App\Http\Controllers;

use App\Http\Requests\RemindersRequest;
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

    public function store(RemindersRequest $request)
    {
        request()->user()->reminders()->save(
            new Reminder([
                'title' => $request->title,
                'due_at' => Carbon::parse($request->date . " " . $request->time)->seconds(0),
            ])
        );

        return redirect()->back();
    }

    public function update(RemindersRequest $request, Reminder $reminder)
    {
        $reminder->update([
            'title' => $request->title,
            'due_at' => Carbon::parse($request->date . " " . $request->time)->seconds(0),
        ]);
    }

    public function destroy(RemindersRequest $request, Reminder $reminder)
    {
        $reminder->delete();
        return redirect()->back();
    }
}
