@extends('layouts.app')

@section('content')
    <div class="max-w-lg px-8 mx-auto">
        <form action="{{ route('reminders.store') }}" method="post">
            @csrf

            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">
                    {{ __('New reminder') }}:
                </label>

                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline{{ $errors->has('email') ? ' border-red-500' : '' }}" name="title" value="{{ old('title') }}" required autofocus>

                @if ($errors->has('title'))
                    <p class="text-red-500 text-xs italic mt-4">
                        {{ $errors->first('title') }}
                    </p>
                @endif
            </div>
            <div class="flex -mx-2">
                <div class="flex-1 mx-2">
                    <div class="mb-4">
                        <label for="date" class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('Date') }}:
                        </label>

                        <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline{{ $errors->has('email') ? ' border-red-500' : '' }}" name="date" value="{{ old('date') }}" required>

                        @if ($errors->has('date'))
                            <p class="text-red-500 text-xs italic mt-4">
                                {{ $errors->first('date') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex-1 mx-2">
                    <div class="mb-4">
                        <label for="time" class="block text-gray-700 text-sm font-bold mb-2">
                            {{ __('Time') }}:
                        </label>

                        <input type="time" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline{{ $errors->has('email') ? ' border-red-500' : '' }}" name="time" value="{{ old('time') }}" required>

                        @if ($errors->has('time'))
                            <p class="text-red-500 text-xs italic mt-4">
                                {{ $errors->first('time') }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="text-right">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-gray-100 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    {{ __('Create') }}
                </button>
            </div>
        </form>
    </div>
    @if($reminders->count() > 0)
        <div class="w-full sm:w-1/2 3 mx-auto px-4 border-t-2 mt-16 pt-8">
            <h2 class="font-semibold text-lg uppercase tracking-wide">Reminders</h2>
            @php $last_date = ''; @endphp
            @foreach ($reminders as $reminder)
                @if($last_date != $reminder->due_at->toDateString())
                    <div class="mt-6 font-semibold">{{ $reminder->due_at->toDayDateTimeString() }}</div>
                @endif
                <div class="flex items-baseline mt-2">
                    <div>{{ $reminder->due_at->format('h:ia') }}</div>
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
