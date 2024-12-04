@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h3>Nieuwe Beker Aanmaken</h3>
            
            <form action="{{ route('cups.store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="name">Naam van de Beker</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="season_id">Seizoen</label>
                    <select name="season_id" class="form-control" required>
                        @foreach($seasons as $season)
                            <option value="{{ $season->id }}">{{ $season->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="division_id">Divisie</label>
                    <select name="division_id" class="form-control" required>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Beker Aanmaken</button>
            </form>
        </div>
    </div>
</div>
@endsection
