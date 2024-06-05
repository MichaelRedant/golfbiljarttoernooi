{{-- resources/views/dashboard/default.blade.php --}}
@extends('layouts.app')

@section('header')
<h2 class="font-semibold text-xl leading-tight">
    {{ __('Dashboard') }}
</h2>
@endsection

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title">Welkom {{ auth()->user()->name }}</h5>
                    @if (auth()->user()->profile_photo_path)
                        <img src="{{ Storage::url(auth()->user()->profile_photo_path) }}" alt="Profile Photo" class="img-thumbnail mb-3">
                    @else
                        <img src="{{ asset('default-profile.png') }}" alt="Default Profile Photo" class="img-thumbnail mb-3">
                    @endif
                    <a href="{{ route('profile.edit', auth()->user()) }}" class="btn btn-primary">
                        <i class="fas fa-user-edit"></i> Bewerk Profiel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
