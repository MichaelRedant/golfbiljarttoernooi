@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Bevestig je emailadres') }}</div>

                <div class="card-body">
                    <p class="text-muted">
                        {{ __('Bedankt voor je aanmelding! Voordat je begint, kun je je e-mailadres verifiëren door op de link te klikken die we zojuist naar je hebben gemaild? Als je de e-mail niet hebt ontvangen, sturen we je graag nog een keer') }}
                    </p>

                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success" role="alert">
                            {{ __('Er is een nieuwe verificatielink verstuurd naar het e-mailadres dat je hebt opgegeven tijdens de registratie') }}
                        </div>
                    @endif

                    <div class="mt-4 d-flex justify-content-between align-items-center">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                {{ __('Stuur verificatie mail opnieuw') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link text-muted">
                                {{ __('Uitloggen') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
