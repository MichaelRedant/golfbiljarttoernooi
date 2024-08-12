@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="text-center mb-4">
        <h1>Golfbiljart Live Scores</h1>
        <button id="refreshButton" class="btn btn-primary btn-lg">
            <i class="fas fa-sync-alt"></i> Refresh Scores
        </button>
    </div>
    <div id="liveScores">
        @if($matches->isEmpty())
            <p class="text-center">Er zijn momenteel geen live wedstrijden.</p>
        @else
            @foreach($matches->groupBy('division.name') as $division => $divisionMatches)
                <div class="card mb-3 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            {{ $division }}
                        </h4>
                        <button class="btn btn-sm btn-outline-secondary toggle-division" data-division="{{ Str::slug($division) }}">Toggle</button>
                    </div>
                    <div class="card-body division-details" data-division="{{ Str::slug($division) }}">
                        @foreach($divisionMatches as $match)
                            @if($match->homeTeam && $match->awayTeam)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <a href="{{ route('teams.show', $match->homeTeam->id) }}" class="text-decoration-none text-dark">{{ $match->homeTeam->name }}</a>
                                            <span class="mx-2">vs</span>
                                            <a href="{{ route('teams.show', $match->awayTeam->id) }}" class="text-decoration-none text-dark">{{ $match->awayTeam->name }}</a>
                                        </h5>
                                    </div>
                                    <div class="mt-2">
                                        <p class="card-text text-center">
                                            <strong>Score:</strong> 
                                            <span class="home-score" data-match-id="{{ $match->id }}">{{ $match->home_score }}</span> - 
                                            <span class="away-score" data-match-id="{{ $match->id }}">{{ $match->away_score }}</span>
                                        </p>
                                        @if ($match->forfeit_by)
                                            <p class="card-text text-center text-danger forfeit" data-match-id="{{ $match->id }}">
                                                <strong>Forfeit by:</strong>
                                                {{ $match->forfeit_by === 'home' ? $match->homeTeam->name : $match->awayTeam->name }}
                                            </p>
                                        @endif
                                        <p class="card-text text-center">
                                            <small class="text-muted">Laatste update: <span class="last-updated" data-match-id="{{ $match->id }}">{{ $match->updated_at->setTimezone('Europe/Brussels')->format('H:i:s') }}</span></small>
                                        </p>
                                    </div>
                                </div>
                                <hr>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function fetchLiveScores() {
        $.ajax({
            url: '{{ route('games.fetchLiveScores') }}',
            method: 'GET',
            success: function(data) {
                data.matches.forEach(function(match) {
                    const homeScoreElement = document.querySelector(`.home-score[data-match-id="${match.match_id}"]`);
                    const awayScoreElement = document.querySelector(`.away-score[data-match-id="${match.match_id}"]`);
                    const lastUpdatedElement = document.querySelector(`.last-updated[data-match-id="${match.match_id}"]`);
                    const forfeitElement = document.querySelector(`.forfeit[data-match-id="${match.match_id}"]`);

                    if (homeScoreElement && awayScoreElement && lastUpdatedElement) {
                        homeScoreElement.textContent = match.home_score;
                        awayScoreElement.textContent = match.away_score;
                        lastUpdatedElement.textContent = match.updated_at;
                        if (match.forfeit_by) {
                            if (!forfeitElement) {
                                const newForfeitElement = document.createElement('p');
                                newForfeitElement.classList.add('card-text', 'text-center', 'text-danger', 'forfeit');
                                newForfeitElement.setAttribute('data-match-id', match.match_id);
                                newForfeitElement.innerHTML = `<strong>Forfeit by:</strong> ${match.forfeit_by === 'home' ? match.home_team_name : match.away_team_name}`;
                                homeScoreElement.parentElement.insertBefore(newForfeitElement, lastUpdatedElement);
                            } else {
                                forfeitElement.innerHTML = `<strong>Forfeit by:</strong> ${match.forfeit_by === 'home' ? match.home_team_name : match.away_team_name}`;
                            }
                        } else if (forfeitElement) {
                            forfeitElement.remove();
                        }
                    }
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching live scores:', error, xhr);
            }
        });
    }

    document.getElementById('refreshButton').addEventListener('click', function() {
        location.reload();
    });

    setInterval(fetchLiveScores, 10000); // Refresh every 10 seconds
    fetchLiveScores(); // Initial fetch to populate scores on page load

    document.querySelectorAll('.toggle-division').forEach(button => {
        button.addEventListener('click', function() {
            const divisionSlug = this.getAttribute('data-division');
            const divisionDetails = document.querySelector(`.division-details[data-division="${divisionSlug}"]`);
            if (divisionDetails) {
                divisionDetails.classList.toggle('d-none');
            }
        });
    });
});
</script>

@endsection
