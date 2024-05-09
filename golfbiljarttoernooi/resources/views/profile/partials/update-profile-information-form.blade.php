@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2>{{ __('Profiel aanpassen') }}</h2>
        </div>
        <div class="card-body">
            <!-- Profile Update Form -->
            <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')  <!-- Zorg dat dit overeenkomt met je route methode -->

                <div class="mb-3">
                    <label for="name" class="form-label">{{ __('Naam') }}</label>
                    <input id="name" type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                    @error('name')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <{{-- div class="mb-3">
                    <label for="profile_photo" class="form-label">{{ __('Profiel foto') }}</label>
                    <input id="profile_photo" type="file" class="form-control" name="profile_photo">
                </>
 --}}
                <div class="d-flex align-items-center">
                    <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
