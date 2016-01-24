@extends('layouts.master')

@section('top')

<div class="uk-navbar">
    <div class="uk-container uk-container-center">
        <a href="/" class="uk-navbar-brand">
            <img src="/assets/logo-long@2x.png" alt="Logo" width="200">
        </a>

        <div class="uk-navbar-flip">
            <ul class="uk-navbar-nav">
                @if (Auth::check())
                    <li><a href="/{{ Auth::user()->handle }}">Profile</a></li>
                    <li><a href="{{ URL::route('settings') }}">Settings</a></li>
                    <li><a href="{{ URL::route('auth::logout') }}">Logout</a></li>
                @else
                    <li><a href="{{ URL::route('auth::login') }}">Login</a></li>
                @endif
            </ul>
        </div>
    </div>
</div>

@stop
