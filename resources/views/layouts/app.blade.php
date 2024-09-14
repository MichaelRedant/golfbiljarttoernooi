<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Golfbiljart Verbond Aalst') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon"/>
    <!-- Fonts en Styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito">
    <link href="{{ asset('css/stijl.css') }}" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="font-sans antialiased">
    <!-- Navigation -->
    @include('layouts.navigation')

    <!-- Page Heading -->
    @if (isset($header))
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endif

    <!-- Flash Messages -->
    @include('partials.flash-messages')

     <!-- Sponsors Navbar -->
     @include('layouts.sponsor-navbar', ['sponsors' => $sponsors])

    <!-- Page Content -->
    <main class="content-wrapper">
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <a href="https://pixapop.be" target="_blank">
                <img src="{{ asset('images/Pixapop_black.webp') }}" alt="Pixapop Logo" class="footer-logo">
            </a>
            <p class="footer-text">Designed & created by <a href="https://pixapop.be" target="_blank">Pixapop webdesign</a> © <span id="current-year"></span></p>
        </div>
    </footer>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
   

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
         // Dynamically set the current year in the footer
         document.addEventListener('DOMContentLoaded', function() {
            const yearSpan = document.getElementById('current-year');
            yearSpan.textContent = new Date().getFullYear();
        });
    </script>
</body>
</html>
