@extends('layouts.app')

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
                <div class="card-body text-center">
                    <h5 class="card-title">Welkom {{ auth()->user()->name }}</h5>
                    <a href="{{ route('profile.edit', auth()->user()) }}" class="btn btn-primary">
                        <i class="fas fa-user-edit"></i> Bewerk Profiel
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-calendar-alt"></i> Wedstrijden</h5>
                    
                    <a href="{{ route('games.create', ['division_id' => $divisions->first()->id, 'season_id' => $currentSeason->id]) }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-calendar-plus"></i> Plan Wedstrijd
                    </a>
                    @if(isset($divisions) && $divisions->isNotEmpty())
                        @foreach($divisions as $division)
                            <a href="{{ route('games.for-division-season', ['division_id' => $division->id, 'season_id' => $currentSeason->id]) }}" class="btn btn-outline-secondary d-block mb-2">
                                <i class="fas fa-eye"></i> Bekijk Wedstrijden van {{ $division->name }}
                            </a>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-users"></i> Gebruikersbeheer</h5>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-primary d-block mb-2">
                        <i class="fas fa-users"></i> Bekijk Gebruikers
                    </a>
                    <a href="{{ route('users.create') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-user-plus"></i> Nieuwe Gebruiker
                    </a>
                </div>
            </div>
        </div>
        
        <!-- New Section for Quick Edit Access -->
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-tools"></i> Snel Bewerken</h5>
                    <a href="{{ route('teams.index') }}" class="btn btn-outline-primary d-block mb-2">
                        <i class="fas fa-edit"></i> Bewerken Teams
                    </a>
                    <a href="{{ route('players.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-edit"></i> Bewerken Spelers
                    </a>
                    <a href="{{ route('divisions.index') }}" class="btn btn-outline-primary d-block mb-2">
                        <i class="fas fa-edit"></i> Bewerken Divisies
                    </a>
                    <a href="{{ route('seasons.index') }}" class="btn btn-outline-secondary d-block mb-2">
                        <i class="fas fa-edit"></i> Bewerken Seizoenen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
