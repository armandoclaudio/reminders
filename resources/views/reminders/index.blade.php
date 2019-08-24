@extends('layouts.app')

@section('content')
    <div class="container px-6 mx-auto">
        <form action="{{ route('reminders.store') }}" method="post">
            @csrf

            <div class="flex flex-wrap md:flex-no-wrap justify-end">
                <div class="w-full md:w-1/2">
                    <div class="mb-4 md:mb-0">
                        <input type="text" class="h-10 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline{{ $errors->has('email') ? ' border-red-500' : '' }}" name="title" value="{{ old('title') }}" placeholder="@lang('New reminder')" required autofocus>

                        @if ($errors->has('title'))
                            <p class="text-red-500 text-xs italic mt-4">
                                {{ $errors->first('title') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex-1 md:ml-4">
                    <div class="mb-4 sm:mb-0">
                        <label for="date" class="block md:hidden text-gray-700 text-sm font-bold mb-2">
                            {{ __('Date') }}:
                        </label>

                        <input type="date" class="bg-white h-10 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline{{ $errors->has('email') ? ' border-red-500' : '' }}" name="date" value="{{ old('date') }}" placeholder="@lang('Date')" required>

                        @if ($errors->has('date'))
                            <p class="text-red-500 text-xs italic mt-4">
                                {{ $errors->first('date') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex-1 ml-4">
                    <div class="mb-4 sm:mb-0">
                        <label for="date" class="block md:hidden text-gray-700 text-sm font-bold mb-2">
                            {{ __('Time') }}:
                        </label>

                        <input type="time" class="bg-white h-10 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline{{ $errors->has('email') ? ' border-red-500' : '' }}" name="time" value="{{ old('time') }}" placeholder="@lang('Time')" required>

                        @if ($errors->has('time'))
                            <p class="text-red-500 text-xs italic mt-4">
                                {{ $errors->first('time') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="w-full sm:w-auto sm:ml-4 text-right">
                    <button type="submit" class="h-10 bg-blue-500 hover:bg-blue-700 text-gray-100 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        {{ __('Create') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
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
