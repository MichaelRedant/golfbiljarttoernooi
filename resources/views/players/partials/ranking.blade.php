<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Naam</th>
            <th>Team</th>
            <th>Gewonnen</th>
            <th>Verloren</th>
            <th>Punten</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($standings as $index => $standing)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $standing['player_name'] }}</td>
                <td>{{ $standing['team_name'] }}</td>
                <td>{{ $standing['matches_won'] }}</td>
                <td>{{ $standing['matches_lost'] }}</td>
                <td>{{ $standing['points'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
