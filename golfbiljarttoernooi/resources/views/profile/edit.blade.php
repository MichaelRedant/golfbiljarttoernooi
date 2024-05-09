@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <!-- Update Profile Information Form -->
                    <form method="POST" action="{{ route('users.update', auth()->user()) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Naam') }}</label>
                            <input id="name" type="text" class="form-control" name="name" value="{{ old('name', auth()->user()->name) }}" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="profile_photo" class="form-label">{{ __('Profielfoto') }}</label>
                            <input id="profile_photo" type="file" class="form-control" name="profile_photo">
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Profiel Bijwerken') }}</button>
                           
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <!-- Update Password Form -->
                    <form method="POST" action="{{ route('password.update') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="current_password" class="form-label">{{ __('Huidig Wachtwoord') }}</label>
                            <input id="current_password" type="password" class="form-control" name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">{{ __('Nieuw Wachtwoord') }}</label>
                            <input id="new_password" type="password" class="form-control" name="new_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">{{ __('Bevestig Nieuw Wachtwoord') }}</label>
                            <input id="new_password_confirmation" type="password" class="form-control" name="new_password_confirmation" required>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">{{ __('Wachtwoord Bijwerken') }}</button>
                           
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <!-- Delete User Form -->
                    <form method="POST" action="{{ route('users.destroy') }}">
                        @csrf
                        @method('DELETE')

                        <div class="mb-3">
                            <p>{{ __('Weet je zeker dat je je account wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.') }}</p>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-danger">{{ __('Account Verwijderen') }}</button>
                           
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
        
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-secondary mt-4">{{ __('Terug') }}</a>
</div>
@endsection
