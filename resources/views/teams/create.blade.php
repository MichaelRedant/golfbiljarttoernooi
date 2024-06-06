@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Nieuw Team Toevoegen</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('teams.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Teamnaam</label>
                    <input type="text" name="name" class="form-control" id="name" placeholder="Teamnaam" required>
                </div>
                <div class="mb-3">
                    <label for="club_id" class="form-label">Club</label>
                    <select name="club_id" class="form-select" id="club_id" required>
                        <option value="">Selecteer een club</option>
                        @foreach ($clubs as $club)
                            <option value="{{ $club->id }}" {{ isset($clubId) && $clubId == $club->id ? 'selected' : '' }}>
                                {{ $club->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="division_id" class="form-label">Divisie</label>
                    <select name="division_id" class="form-select" id="division_id" required>
                        <option value="">Selecteer een divisie</option>
                        @foreach ($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>
               
                <input type="hidden" name="players" id="selected-players-input">
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
        const availablePlayers = document.getElementById('available-players');
        const selectedPlayers = document.getElementById('selected-players');
        const selectedPlayersInput = document.getElementById('selected-players-input');

        availablePlayers.addEventListener('click', function (e) {
            if (e.target && e.target.nodeName == "LI" && !e.target.classList.contains('team-header')) {
                e.target.classList.toggle('selected');
            }
        });

        selectedPlayers.addEventListener('click', function (e) {
            if (e.target && e.target.nodeName == "LI") {
                e.target.classList.toggle('selected');
            }
        });

        document.getElementById('move-right').addEventListener('click', function () {
            moveItems(availablePlayers, selectedPlayers);
        });

        document.getElementById('move-left').addEventListener('click', function () {
            moveItems(selectedPlayers, availablePlayers);
        });

        function moveItems(from, to) {
            Array.from(from.querySelectorAll('.selected')).forEach(function (item) {
                item.classList.remove('selected');
                to.appendChild(item);
            });
            updateSelectedPlayersInput();
        }

        function updateSelectedPlayersInput() {
            const selectedIds = Array.from(selectedPlayers.querySelectorAll('li')).map(function (item) {
                return item.getAttribute('data-id');
            });
            selectedPlayersInput.value = selectedIds.join(',');
        }
    });
</script>
@endsection
