{{-- resources/views/teams/addresses.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Locaties van Teams</h1>
    <select id="club-select" class="form-control mb-3">
        <option value="">Selecteer een club</option>
        @foreach($clubs as $club)
            <option value="{{ $club->id }}">{{ $club->name }}</option>
        @endforeach
    </select>
    <ul class="list-group" id="team-list"></ul>
</div>

<script>
document.getElementById('club-select').addEventListener('change', function() {
    var clubId = this.value;
    var teamList = document.getElementById('team-list');
    teamList.innerHTML = ''; // Clear existing list

    if (clubId) {
        fetch(`/api/teams/by-club/${clubId}`)
            .then(response => response.json())
            .then(teams => {
                teams.forEach(team => {
                    let li = document.createElement('li');
                    li.classList.add('list-group-item');
                    li.innerHTML = `<a href="{{ url('/teams') }}/${team.id}" class="text-decoration-none">${team.name} - ${team.location}</a>`;
                    teamList.appendChild(li);
                });
            })
            .catch(error => {
                console.error('Error loading the teams:', error);
            });
    }
});
</script>
@endsection
