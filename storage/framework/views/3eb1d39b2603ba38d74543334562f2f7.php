<?php $__env->startSection('content'); ?>
<div class="container card">
    <h1>Team Klassement</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Team</th>
                <th>Gewonnen</th>
                <th>Verloren</th>
                <th>Gelijk</th>
                <th>Punten</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $standings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $standing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><a href="<?php echo e(route('teams.show', ['team' => $standing['team_id']])); ?>"><?php echo e($standing['team_name']); ?></a></td>
                    <td><?php echo e($standing['games_won']); ?></td>
                    <td><?php echo e($standing['games_lost']); ?></td>
                    <td><?php echo e($standing['games_draw']); ?></td>
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

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/teams/standings.blade.php ENDPATH**/ ?>