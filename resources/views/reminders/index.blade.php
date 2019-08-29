@extends('layouts.app')

@section('content')
    <div class="container px-6 mx-auto">
        <reminders-form action="{{ route('reminders.store') }}"></reminders-form>
    </div>

    <reminders-list url="{{ route('reminders.index') }}"></reminders-list>
    @if($reminders->count() > 0)
        <div class="container mx-auto px-4 mt-12">
            <h2 class="font-semibold text-sm uppercase tracking-wide">@lang('Scheduled Reminders')</h2>
            @php $last_date = ''; @endphp
            @foreach ($reminders as $reminder)
                @if($last_date != $reminder->due_at->toDateString())
                    <div class="mt-6 font-semibold">{{ $reminder->due_at->toDayDateTimeString() }}</div>
                @endif
                <div class="flex items-baseline mt-2">
                    <div class="uppercase text-xs font-semibold text-blue-700">{{ $reminder->due_at->format('h:ia') }}</div>
                    <div class="ml-2">{{ $reminder->title }}</div>
                    <a class="ml-4 lowercase text-xs text-red-600 hover:text-red-800" href="#" onclick="event.preventDefault();document.getElementById('reminder-{{ $reminder->id }}-delete').submit()">Delete</a>

                    <form id="reminder-{{ $reminder->id }}-delete" action="{{ route('reminders.destroy', $reminder->id) }}" method="post">
                        @method('DELETE')
                        @csrf
                    </form>
                </div>
                @php $last_date = $reminder->due_at->toDateString(); @endphp
            @endforeach
        </div>
    @endif
@endsection
