@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl leading-tight">
    {{ __('Sponsors Beheer') }}
</h2>
@endsection

@section('content')
<div class="container py-5 mt-4">
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('sponsors.create') }}" class="btn btn-primary mb-3">Nieuwe Sponsor Toevoegen</a>
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <div class="card">
                <div class="card-header">Sponsors</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Naam</th>
                                <th>Afbeelding</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sponsors as $sponsor)
                                <tr>
                                    <td>{{ $sponsor->name }}</td>
                                    <td><img src="{{ asset($sponsor->image_path) }}" alt="{{ $sponsor->name }}" width="100"></td>
                                    <td>
                                        <a href="{{ route('sponsors.edit', $sponsor->id) }}" class="btn btn-warning">Bewerken</a>
                                        <form action="{{ route('sponsors.destroy', $sponsor->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Verwijderen</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
