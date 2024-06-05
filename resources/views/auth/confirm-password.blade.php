@section('content')
<div class="container mt-4">
    <div class="mb-4 text-sm text-gray-600">
       Bevestig je wachtwoord
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('Wachtwoord') }}</label>
            <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary">{{ __('Confirm') }}</button>
        </div>
    </form>
</div>
@endsection
