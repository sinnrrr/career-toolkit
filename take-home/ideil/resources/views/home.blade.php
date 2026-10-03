@extends('layouts.app')

@section('content')
    <div class="container">
        <Dashboard
            :user="{{ \Illuminate\Support\Facades\Auth::user() }}"
            :markers="{{ $markerData }}"></Dashboard>
    </div>
@endsection
