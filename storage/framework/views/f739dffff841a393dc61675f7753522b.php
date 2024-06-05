<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Wedstrijddetails voor <a href="<?php echo e(route('teams.show', $game->homeTeam->id)); ?>" style="color: <?php echo e($game->home_score > $game->away_score ? 'green' : 'red'); ?>;"><?php echo e($game->homeTeam->name); ?></a> vs <a href="<?php echo e(route('teams.show', $game->awayTeam->id)); ?>" style="color: <?php echo e($game->away_score > $game->home_score ? 'green' : 'red'); ?>;"><?php echo e($game->awayTeam->name); ?></a></h1>
    <div>
        <p>Thuisploeg: <a href="<?php echo e(route('teams.show', $game->homeTeam->id)); ?>" style="color: <?php echo e($game->home_score > $game->away_score ? 'green' : 'red'); ?>;"><?php echo e($game->homeTeam->name); ?></a></p>
        <p>Bezoekers: <a href="<?php echo e(route('teams.show', $game->awayTeam->id)); ?>" style="color: <?php echo e($game->away_score > $game->home_score ? 'green' : 'red'); ?>;"><?php echo e($game->awayTeam->name); ?></a></p>
        <p>Datum: <?php echo e($game->date->format('d-m-Y')); ?></p>
        <p>Wedstrijdscore: <strong><?php echo e($game->home_score); ?> - <?php echo e($game->away_score); ?></strong></p>
        <a href="<?php echo e(route('games.index')); ?>" class="btn btn-primary">Terug naar Wedstrijdkalender</a>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Thuis Speler</th>
                    <th>Uit Speler</th>
                    <th>1M</th>
                    <th>2M</th>
                    <th>Belle</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $game->manches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $manche): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($loop->index + 1); ?></td>
                    <td><a href="<?php echo e(route('players.show', $manche->player1->id)); ?>"><?php echo e($manche->player1->first_name); ?> <?php echo e($manche->player1->last_name); ?></a></td>
                    <td><a href="<?php echo e(route('players.show', $manche->player2->id)); ?>"><?php echo e($manche->player2->first_name); ?> <?php echo e($manche->player2->last_name); ?></a></td>
                    <td><?php echo e($manche->score1); ?></td>
                    <td><?php echo e($manche->score2); ?></td>
                    <td><?php echo e($manche->belle_score ?? ''); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/games/show.blade.php ENDPATH**/ ?>