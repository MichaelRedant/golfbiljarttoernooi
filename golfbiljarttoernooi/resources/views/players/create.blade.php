@extends('layouts.app')

@section('content')
    <h1>Nieuwe Speler Toevoegen</h1>

    <form action="{{ route('players.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label for="first_name">Voornaam:</label>
            <input type="text" name="first_name" class="form-control" id="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Achternaam:</label>
            <input type="text" name="last_name" class="form-control" id="last_name" required>
        </div>
        <div class="form-group">
            <label for="division_id">Divisie:</label>
            <select name="division_id" class="form-control" id="division_id" required>
                <option value="">Selecteer een divisie</option>
                @foreach ($divisions as $division)
                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="team">Team:</label>
            <select name="team_id" required>
                <option value="">Selecteer een team</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#division_id').on('change', function () {
                var divisionId = $(this).val();
                if (divisionId) {
                    $.ajax({
                        type: 'GET',
                        url: '{{ route('get-teams') }}',
                        data: {division_id: divisionId},
                        success: function (teams) {
                            $('#team').empty();
                            $.each(teams, function (key, value) {
                                $('#team').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#team').empty();
                }
            });
        });
    </script>
@endsection
