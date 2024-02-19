@extends('layouts.app')

@section('title', 'Wedstrijdkalender')

@section('content')
<div class="container mt-4">
    <h1>Wedstrijdkalender</h1>
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
            // Hier kan je een actie definiëren wanneer op een event geklikt wordt, bijv. redirect naar game details
            window.location.href = info.event.url;
            info.jsEvent.preventDefault(); // voorkomt dat de browser naar de link navigeert
        }
    });
    calendar.render();
});
</script>
@endsection

  