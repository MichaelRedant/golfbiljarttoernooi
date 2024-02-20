@extends('layouts.app')

@section('title', 'Wedstrijdkalender')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijdkalender</h1>
    
    <!-- Actieknoppen -->
    <div class="mb-4">
        <form action="{{ route('games.generate') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary">Genereer Wedstrijden</button>
        </form>
        <a href="{{ route('games.clear') }}" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je de kalender wilt verwijderen? Dit kan niet ongedaan gemaakt worden.');">Kalender Verwijderen</a>
    </div>

    <!-- Kalender -->
    <div id='calendar'></div>
</div>

<!-- Voeg FullCalendar script en stijlen toe -->
<link href='{{ asset('fullcalendar/main.css') }}' rel='stylesheet' />
<script src='{{ asset('fullcalendar/main.js') }}'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '{{ route("games.calendar-data") }}',
        eventClick: function(info) {
            // Actie bij klikken op een event
            window.location.href = info.event.url;
            info.jsEvent.preventDefault(); // Voorkomt dat de browser naar de link navigeert
        }
    });
    calendar.render();
});
</script>
@endsection
