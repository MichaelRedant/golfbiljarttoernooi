@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="d-flex align-items-center my-4">
                <img src="{{ asset('images/logoGVA.png') }}" alt="GVA Logo" class="logo mr-3">
                <h1 class="text-center">Welkom bij onze Golfbiljart Applicatie</h1>
            </div>
            <p class="text-justify">
                Golfbiljart is een fascinerende sport die precisie, tactiek en vaardigheid combineert. Het wordt gespeeld op een speciale biljarttafel, waarbij het doel is om de ballen in een specifieke volgorde te raken en punten te scoren. Onze applicatie helpt liefhebbers van de sport om wedstrijden te organiseren, scores bij te houden en meer te leren over verschillende teams en spelers.
                <br> <br>
                Verken onze wedstrijdkalender om de aankomende evenementen te zien, duik in de details van verschillende reeksen, of bekijk de prestaties van teams en spelers door onze uitgebreide rankings. Of je nu een speler, coach of gewoon een fan bent, onze app biedt iets voor iedereen.
            </p>
            <div class="row justify-content-center my-4">
                <div class="col-md-8 d-flex flex-wrap justify-content-center gap-3">
                    <a href="{{ route('divisions.index') }}" class="btn btn-primary animated-btn m-3">Reeksen</a>
                    <a href="{{ route('clubs.index') }}" class="btn btn-primary animated-btn m-3">Clubs</a>
                    <a href="{{ route('teams.index') }}" class="btn btn-primary animated-btn m-3">Teams</a>
                    <a href="{{ route('players.index') }}" class="btn btn-primary animated-btn m-3">Spelers</a>
                    <a href="{{ route('rankings.index') }}" class="btn btn-primary animated-btn m-3">Rankings</a>
                    <a href="{{ route('live-scores') }}" class="btn btn-primary animated-btn m-3">Live Wedstrijden</a>
                </div>
            </div>
            
            <!-- Nieuws sectie -->
            <section id="news-section" class="card mb-4 mt-4">
                <div class="card-header">
                    <h2 class="card-title">Laatste Nieuws</h2>
                </div>
                
                <!-- Sticky nieuws sectie -->
                <div class="news-items sticky-news">
                    @foreach ($news->where('is_sticky', 1) as $newsItem)
                        <div class="news-item m-4 sticky">
                            <h3>
                                {{ $newsItem->title }}
                                <i class="fas fa-thumbtack" title="Sticky"></i>
                            </h3>
                            <p class="news-date">{{ $newsItem->created_at->format('d-m-Y') }}</p>
                            <div class="news-content">
                                {!! Str::limit($newsItem->content, 150) !!}
                                @if(strlen($newsItem->content) > 150)
                                    <span class="news-more" style="display: none;">{!! substr($newsItem->content, 150) !!}</span>
                                    <a href="javascript:void(0)" class="news-read-more">Lees meer</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Normale nieuws sectie -->
                <div class="news-items normal-news">
                    @foreach ($news->where('is_sticky', 0) as $newsItem)
                        <div class="news-item m-4">
                            <h3>{{ $newsItem->title }}</h3>
                            <p class="news-date">{{ $newsItem->created_at->format('d-m-Y') }}</p>
                            <div class="news-content">
                                {!! Str::limit($newsItem->content, 150) !!}
                                @if(strlen($newsItem->content) > 150)
                                    <span class="news-more" style="display: none;">{!! substr($newsItem->content, 150) !!}</span>
                                    <a href="javascript:void(0)" class="news-read-more">Lees meer</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            
            <div class="card mb-4 mt-4">
                <div class="card-header">
                    <h2 class="card-title">Wat is Golfbiljart?</h2>
                </div>
                <div class="card-body">
                    <p class="text-justify">
                        Golfbiljart is een biljartspel dat zich onderscheidt door zijn unieke regels en speelmethode. Het spel vereist strategisch inzicht en een vaste hand om succesvol te zijn. Elk spel bestaat uit verschillende rondes waarin spelers moeten proberen hun ballen in de juiste volgorde te potten.
                        <br> <br>
                        Golfbiljart wordt vaak gespeeld in competitieverband, met spelers die strijden om de hoogste eer binnen hun reeks. Deze applicatie biedt alle tools die nodig zijn om competities te beheren en de prestaties van spelers en teams te volgen.
                    </p>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title">Hoe te beginnen?</h2>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">Bekijk de <a href="{{ route('divisions.index') }}">reeksen</a> en kies je favoriete teams.</li>
                        <li class="list-group-item">Volg je favoriete <a href="{{ route('teams.index') }}">teams</a> en <a href="{{ route('players.index') }}">spelers</a>.</li>
                        <li class="list-group-item">Blijf op de hoogte van de laatste <a href="{{ route('rankings.index') }}">rankings</a> en prestaties.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Video sectie -->
    <div class="row">
        <div class="col-md-12">
            <div class="video-container my-4">
                <video autoplay loop muted class="home-video">
                    <source src="{{ asset('images/billiards.mp4') }}" type="video/mp4">
                    Uw browser ondersteunt geen video tag.
                </video>
            </div>
        </div>
    </div>
</div>

<style>
     .logo {
        max-height: 150px;
        margin-right: 10px;
    }
    .news-content {
        position: relative;
    }

    .news-more {
        display: none;
    }

    .news-read-more {
        color: #007bff;
        cursor: pointer;
        display: inline-block;
        margin-top: 10px;
    }

    .news-item {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        position: relative;
    }

    .news-item.sticky {
        border-left: 4px solid #ffcc00;
    }

    .news-item .fa-thumbtack {
        color: red;
        margin-left: 10px;
        font-size: 0.8em;
    }

    .news-date {
        font-size: 0.9em;
        color: #888;
        margin-top: -10px;
        margin-bottom: 10px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const readMoreLinks = document.querySelectorAll('.news-read-more');
        readMoreLinks.forEach(link => {
            link.addEventListener('click', function () {
                const content = this.previousElementSibling;
                if (content.style.display === 'none' || content.style.display === '') {
                    content.style.display = 'inline';
                    this.textContent = 'Lees minder';
                } else {
                    content.style.display = 'none';
                    this.textContent = 'Lees meer';
                }
            });
        });
    });
</script>
@endsection
