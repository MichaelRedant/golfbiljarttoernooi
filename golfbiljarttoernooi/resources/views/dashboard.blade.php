{{-- resources/views/dashboard.blade.php --}}
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
                <div class="card-body">
                    <h5 class="card-title">Welkom {{ auth()->user()->name }}</h5>
                    @if (auth()->user()->profile_photo_path)
                        <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="Profile Photo" class="img-thumbnail">
                    @else
                        <img src="{{ asset('default-profile.png') }}" alt="Default Profile Photo" class="img-thumbnail">
                    @endif
                    <a href="{{ route('profile.edit', auth()->user()) }}" class="btn btn-primary">Bewerk Profiel</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Beheer</h5>
                    <a href="{{ route('games.create', [
    'division_id' => $divisions->first()->id, 
    'season_id' => $currentSeason->id
]) }}" class="btn btn-outline-secondary d-block mb-2">
    <i class="fas fa-calendar-plus"></i> Plan Wedstrijd
</a>

                   @if(isset($divisions) && $divisions->isNotEmpty())
                   @foreach($divisions as $division)
                       <a href="{{ route('games.for-division-season', ['division_id' => $division->id, 'season_id' => $currentSeason->id]) }}" class="btn btn-outline-secondary">
                           Bekijk Wedstrijden van {{ $division->name }}
                       </a>
                   @endforeach
               @endif
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('players.create') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-user-plus"></i> Nieuwe Speler</a>
                    <a href="{{ route('teams.create') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-users-cog"></i> Nieuw Team</a>
                    <a href="{{ route('divisions.create') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-layer-group"></i> Nieuwe Divisie</a>
                    <a href="{{ route('seasons.create') }}" class="btn btn-outline-secondary d-block mb-2"><i class="fas fa-calendar-alt"></i> Nieuw Seizoen</a>
                    <a href="{{ route('rankings.index') }}" class="btn btn-outline-success d-block"><i class="fas fa-list-ol"></i> Rankings</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Acties</h5>
                   
                    <a href="{{ route('rankings.index') }}" class="btn btn-outline-success d-block mb-2"><i class="fas fa-list-ol"></i> Rankings</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
