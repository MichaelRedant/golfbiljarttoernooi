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
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Opslaan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
