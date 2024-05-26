@extends('layouts.app')

@section('title', 'Seizoen Bewerken')

@section('content')
<div class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">Seizoen Bewerken</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('seasons.update', $season) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Seizoensnaam</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ $season->name }}" required>
                </div>
                <div class="mb-3">
                    <label for="start_date" class="form-label">Startdatum</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $season->start_date ? $season->start_date->format('Y-m-d') : '' }}" required>
                </div>
                <div class="mb-3">
                    <label for="end_date" class="form-label">Einddatum</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $season->end_date ? $season->end_date->format('Y-m-d') : '' }}" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Bijwerken</button>
                    <a href="{{ route('seasons.index') }}" class="btn btn-secondary mt-2">Terug</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .card {
        margin-top: 20px;
    }
    .form-label {
        font-weight: bold;
    }
    .d-grid {
        display: grid;
        gap: 10px;
    }
</style>
@endsection
