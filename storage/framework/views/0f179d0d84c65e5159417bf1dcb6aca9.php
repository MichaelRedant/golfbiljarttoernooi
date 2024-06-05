<?php $__env->startSection('content'); ?>
<div class="container card">
    <h1>Speler Klassement - Divisie <?php echo e($divisionId); ?></h1>
    <table class="table">
        <thead>
            <tr>
                <th>Speler</th>
                <th>Team</th>
                <th>Wedstrijden Gewonnen</th>
                <th>Wedstrijden Gelijkspel</th>
                <th>Wedstrijden Verloren</th>
                <th>Matches Gewonnen</th>
                <th>Matches Verloren</th>
                <th>Punten</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $standings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $standing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><a href="<?php echo e(route('players.show', ['player' => $standing['player_id']])); ?>"><?php echo e($standing['player_name']); ?></a></td>
                    <td><a href="<?php echo e(route('teams.show', ['team' => $standing['team_id']])); ?>"><?php echo e($standing['team_name']); ?></a></td>
                    <td><?php echo e($standing['games_won']); ?></td>
                    <td><?php echo e($standing['games_drawn']); ?></td>
                    <td><?php echo e($standing['games_lost']); ?></td>
                    <td><?php echo e($standing['matches_won']); ?></td>
                    <td><?php echo e($standing['matches_lost']); ?></td>
                    <td><?php echo e($standing['points']); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <div class="mt-4">
        <a href="<?php echo e(route('rankings.index')); ?>" class="btn btn-primary">Terug naar Overzicht</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/players/standings.blade.php ENDPATH**/ ?>