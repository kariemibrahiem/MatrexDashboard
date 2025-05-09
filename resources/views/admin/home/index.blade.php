@extends('layouts.master')

@section('title')
    {{ config('app.name') }} | {{ __('home') }}
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="card-title text-center">
                <h2>{{ __('general_statistics') }}</h2>
            </div>
        </div>
    </div>
@endsection
