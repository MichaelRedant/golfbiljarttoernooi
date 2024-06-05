<?php $__env->startSection('title', 'Wedstrijdkalender'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h1>Wedstrijdkalender</h1>

    <!-- Seizoen kiezen -->
    <div class="mb-4">
        <?php if($seasons->isNotEmpty()): ?>
            <form action="<?php echo e(route('games.index')); ?>" method="GET">
                <div class="form-group">
                    <label for="season_id">Kies een seizoen:</label>
                    <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                        <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($season->id); ?>" <?php echo e($season->id == $currentSeasonId ? 'selected' : ''); ?>>
                                <?php echo e($season->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </form>
        <?php else: ?>
            <p>Er zijn momenteel geen seizoenen beschikbaar.</p>
        <?php endif; ?>
         <!-- Actieknoppen -->
    <div class="action-buttons mb-4">
        <a href="<?php echo e(route('seasons.index')); ?>" class="btn btn-success">Seizoenen</a>
    </div>

    <!-- Kalender weergave -->
    <?php if($gamesByDate->isNotEmpty()): ?>
        <?php $__currentLoopData = $gamesByDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $gamesOnDate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="day">
                <h2><?php echo e(\Carbon\Carbon::parse($date)->format('d-m-Y')); ?></h2>
                <?php $__currentLoopData = $gamesOnDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="game card">
                        <div class="card-body">
                            <p>
                                <a href="<?php echo e(route('teams.show', $game->homeTeam->id)); ?>" class="font-weight-bold"><?php echo e($game->homeTeam->name); ?></a>
                                tegen
                                <a href="<?php echo e(route('teams.show', $game->awayTeam->id)); ?>" class="font-weight-bold"><?php echo e($game->awayTeam->name); ?></a>
                            </p>
                            <p><span class="font-weight-bold">Uitslag:</span> <?php echo e($game->home_score ?? 'N/A'); ?> : <?php echo e($game->away_score ?? 'N/A'); ?></p>
                            <?php if(is_null($game->home_score) || is_null($game->away_score)): ?>
                                <a href="<?php echo e(route('games.form', $game->id)); ?>" class="btn btn-sm btn-primary">Speel Wedstrijd</a>
                            <?php else: ?>
                                <a href="<?php echo e(route('games.show', $game->id)); ?>" class="btn btn-sm btn-outline-secondary">Bekijk Wedstrijd</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <p>Geen wedstrijden gepland voor dit seizoen.</p>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/games/index.blade.php ENDPATH**/ ?>