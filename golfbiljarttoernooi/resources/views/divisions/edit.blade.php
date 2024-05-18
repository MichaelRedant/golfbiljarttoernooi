@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1><i class="fas fa-edit"></i> Divisie Bewerken: {{ $division->name }}</h1>

    <form action="{{ route('divisions.update', $division) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name"><i class="fas fa-font"></i> Naam:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ $division->name }}">
        </div>

        <div class="form-group">
            <label for="teams"><i class="fas fa-users"></i> Teams in deze Divisie:</label>
            <div class="row">
                <div class="col-md-5">
                    <h5>Beschikbare Teams</h5>
                    <select id="availableTeams" class="form-control" size="10" multiple>
                        @foreach ($teams as $team)
                            @if (!$division->teams->contains($team->id))
                                <option value="{{ $team->id }}">{{ $team->name }} - {{ $team->club->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 text-center d-flex align-items-center justify-content-center">
                    <div>
                        <button type="button" id="addTeam" class="btn btn-primary mb-2"><i class="fas fa-arrow-right"></i></button>
                        <button type="button" id="removeTeam" class="btn btn-danger"><i class="fas fa-arrow-left"></i></button>
                    </div>
                </div>
                <div class="col-md-5">
                    <h5>In {{ $division->name }}</h5>
                    <select id="assignedTeams" name="teams[]" class="form-control" size="10" multiple>
                        @foreach ($teams as $team)
                            @if ($division->teams->contains($team->id))
                                <option value="{{ $team->id }}" selected>{{ $team->name }} - {{ $team->club->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Bijwerken</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('addTeam').addEventListener('click', function () {
        moveSelectedOptions(document.getElementById('availableTeams'), document.getElementById('assignedTeams'));
    });

    document.getElementById('removeTeam').addEventListener('click', function () {
        moveSelectedOptions(document.getElementById('assignedTeams'), document.getElementById('availableTeams'));
    });

    function moveSelectedOptions(sourceSelect, targetSelect) {
        Array.from(sourceSelect.selectedOptions).forEach(option => {
            targetSelect.appendChild(option);
        });
    }
});
</script>
@endsection
