@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Navigation cards on the left side -->
        <div class="col-md-3">
            <div class="nav-flex-column">
                <a href="{{ route('games.index') }}" class="nav-card-link card my-2">Wedstrijdkalender</a>
                <a href="{{ route('divisions.index') }}" class="nav-card-link card my-2">Divisies</a>
                <a href="{{ route('teams.index') }}" class="nav-card-link card my-2">Teams</a>
                <a href="{{ route('players.index') }}" class="nav-card-link card my-2">Spelers</a>
                <a href="{{ route('rankings.index') }}" class="nav-card-link card my-2">Rankings</a>
                <!-- More cards as needed -->
            </div>
        </div>
        <!-- Content section about Golfbiljart -->
        <div class="col-md-9">
            <h1 class="text-center my-4">Welkom bij onze Biljart Applicatie</h1>
            <p class="text-justify">
                Golfbiljart is een fascinerende sport die precisie, tactiek en vaardigheid combineert. 
                Het wordt gespeeld op een speciale biljarttafel, waarbij het doel is om de ballen in een 
                specifieke volgorde te raken en punten te scoren. Onze applicatie helpt liefhebbers van 
                de sport om wedstrijden te organiseren, scores bij te houden en meer te leren over verschillende teams en spelers.
            </p>
            <p class="text-justify">
                Verken onze wedstrijdkalender om de aankomende evenementen te zien, duik in de details van 
                verschillende divisies, of bekijk de prestaties van teams en spelers door onze uitgebreide 
                rankings. Of je nu een speler, coach of gewoon een fan bent, onze app biedt iets voor iedereen.
            </p>
        </div>
    </div>
    <div class="row">
        <!-- Video section -->
    <div class="video-container">
        <video autoplay loop muted class="home-video">
            <source src="{{ asset('images/billiards.mp4') }}" type="video/mp4">
            Uw browser ondersteunt geen video tag.
        </video>
    </div>
    </div>
</div>
@endsection

