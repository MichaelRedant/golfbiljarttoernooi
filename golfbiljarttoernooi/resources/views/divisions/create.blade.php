@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Nieuwe Divisie Toevoegen</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('divisions.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Divisienaam</label>
                    <input type="text" name="name" class="form-control" id="name" placeholder="Divisienaam" required>
                </div>
                <div class="mb-3">
                    <label for="teams" class="form-label">Teams</label>
                    <div class="dual-listbox">
                        <div class="dual-listbox-column">
                            <h5>Beschikbare Teams</h5>
                            <ul id="available-teams" class="list-group">
                                @foreach ($teams->groupBy('division.name') as $divisionName => $teamsGroup)
                                    <li class="list-group-item team-header">{{ $divisionName ?? 'Geen divisie' }}</li>
                                    @foreach ($teamsGroup as $team)
                                        <li class="list-group-item" data-id="{{ $team->id }}">{{ $team->name }}</li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                        <div class="dual-listbox-controls">
                            <button type="button" id="move-right" class="btn btn-primary">&gt;</button>
                            <button type="button" id="move-left" class="btn btn-primary">&lt;</button>
                        </div>
                        <div class="dual-listbox-column">
                            <h5>Geselecteerde Teams</h5>
                            <ul id="selected-teams" class="list-group"></ul>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="teams" id="selected-teams-input">
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .dual-listbox {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .dual-listbox-column {
        flex: 1;
        padding: 10px;
    }
    .dual-listbox-controls {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .list-group {
        height: 200px;
        overflow-y: auto;
    }
    .list-group-item {
        cursor: pointer;
    }
    .list-group-item.selected {
        background-color: #007bff;
        color: white;
    }
    .team-header {
        background-color: #f8f9fa;
        font-weight: bold;
        cursor: default;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const availableTeams = document.getElementById('available-teams');
        const selectedTeams = document.getElementById('selected-teams');
        const selectedTeamsInput = document.getElementById('selected-teams-input');

        availableTeams.addEventListener('click', function (e) {
            if (e.target && e.target.nodeName == "LI" && !e.target.classList.contains('team-header')) {
                e.target.classList.toggle('selected');
            }
        });

        selectedTeams.addEventListener('click', function (e) {
            if (e.target && e.target.nodeName == "LI") {
                e.target.classList.toggle('selected');
            }
        });

        document.getElementById('move-right').addEventListener('click', function () {
            moveItems(availableTeams, selectedTeams);
        });

        document.getElementById('move-left').addEventListener('click', function () {
            moveItems(selectedTeams, availableTeams);
        });

        function moveItems(from, to) {
            Array.from(from.querySelectorAll('.selected')).forEach(function (item) {
                item.classList.remove('selected');
                to.appendChild(item);
            });
            updateSelectedTeamsInput();
        }

        function updateSelectedTeamsInput() {
            const selectedIds = Array.from(selectedTeams.querySelectorAll('li')).map(function (item) {
                return item.getAttribute('data-id');
            });
            selectedTeamsInput.value = selectedIds.join(',');
        }
    });
</script>
@endsection
