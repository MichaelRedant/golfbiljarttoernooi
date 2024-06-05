<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Naam</th>
            <!-- Voeg hier andere kolommen toe zoals gespeelde wedstrijden, gewonnen wedstrijden, enzovoort -->
        </tr>
    </thead>
    <tbody>
        @foreach ($teams as $team)
            <tr>
                <td>{{ $team->id }}</td>
                <td>{{ $team->name }}</td>
                <!-- Voeg hier andere kolommen toe zoals gespeelde wedstrijden, gewonnen wedstrijden, enzovoort -->
            </tr>
        @endforeach
    </tbody>
</table>
