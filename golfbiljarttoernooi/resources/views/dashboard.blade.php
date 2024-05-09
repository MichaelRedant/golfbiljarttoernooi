{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app') {{-- Zorg ervoor dat dit wijst naar je hoofdlayout die Bootstrap bevat --}}

@section('header')
<h2 class="font-semibold text-xl leading-tight">
    {{ __('Dashboard') }}
</h2>
@endsection

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Welkom {{ auth()->user()->name }}</h5>
                    @if (auth()->user()->profile_photo_path)
                        <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="Profile Photo" class="img-thumbnail">
                    @else
                        {{-- <img src="{{ asset('default-profile.png') }}" alt="Default Profile Photo" class="img-thumbnail"> --}}
                    @endif
                    <div>
                        <a href="{{ route('profile.edit', auth()->user()) }}" class="btn btn-primary">Bewerk Profiel</a>


                    </div>
                    
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Beheer</h5>
                    <a href="{{ route('players.create') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-plus"></i> Nieuwe Speler</a>
                    <a href="{{ route('teams.create') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-plus"></i> Nieuw Team</a>
                    <a href="{{ route('divisions.create') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-plus"></i> Nieuwe Divisie</a>
                    <a href="{{ route('seasons.create') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-plus"></i> Nieuw Seizoen</a>
                    <div class="dropdown-divider"></div>
                    @if (auth()->check() && auth()->user()->isAdmin())
                        <a href="{{ route('register') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-user-plus"></i> Registreer Gebruiker</a>
                    @endif
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-users"></i> Teams</a>
                    <a href="{{ route('players.index') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-user"></i> Spelers</a>
                    <a href="{{ route('divisions.index') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-layer-group"></i> Divisies</a>
                    <a href="{{ route('seasons.index') }}" class="btn btn-outline-secondary d-block"><i class="fas fa-calendar-alt"></i> Seizoenen</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Acties</h5>
                    <a href="{{ route('games.index') }}" class="btn btn-outline-success d-block mb-2"><i class="fas fa-gamepad"></i> Wedstrijden</a>
                    <a href="{{ route('rankings.index') }}" class="btn btn-outline-success d-block mb-2"><i class="fas fa-list-ol"></i> Rankings</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
