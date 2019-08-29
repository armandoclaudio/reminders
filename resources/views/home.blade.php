@extends('layouts.app')

@section('content')
    <div class="container px-6 mx-auto">
        <reminders-form action="{{ route('reminders.store') }}"></reminders-form>
    </div>

    <reminders-list url="{{ route('reminders.index') }}"></reminders-list>
@endsection
