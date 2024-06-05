<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <?php if($player->photo): ?>
                <img src="<?php echo e(asset('storage/photos/' . $player->photo)); ?>" alt="Speler Foto" class="rounded-circle mr-3" style="width: 100px; height: 100px;">
            <?php else: ?>
                <img src="<?php echo e(asset('images/placeholder-avatar.png')); ?>" alt="Geen foto beschikbaar" class="rounded-circle mr-3" style="width: 100px; height: 100px;">
            <?php endif; ?>
            <h1 class="h4 mb-0"><?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?></h1>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong><i class="fas fa-users"></i> Team:</strong> <a href="<?php echo e(route('teams.show', $player->team_id)); ?>"><?php echo e($player->team->name); ?></a>
            </div>
            <form action="<?php echo e(route('players.show', $player->id)); ?>" method="GET">
                <div class="form-group">
                    <label for="season_id"><i class="fas fa-calendar-alt"></i> Seizoen:</label>
                    <select name="season_id" id="season_id" class="form-control" onchange="this.form.submit()">
                        <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($season->id); ?>" <?php echo e($season->id == $currentSeasonId ? 'selected' : ''); ?>>
                                <?php echo e($season->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </form>
            <div>
                <p><strong><i class="fas fa-trophy"></i> Gewonnen Matchen:</strong> <?php echo e($matchesWon); ?></p>
                <p><strong><i class="fas fa-thumbs-down"></i> Verloren Matchen:</strong> <?php echo e($matchesLost); ?></p>
                <p><strong><i class="fas fa-list-ol"></i> Plaats Dit Seizoen:</strong> <?php echo e($playerRank); ?></p>
            </div>
            <?php if(auth()->user() && (auth()->user()->role === 'admin' || auth()->user()->role === 'speler')): ?>
                <a href="<?php echo e(route('players.edit', $player->id)); ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Bewerk Speler</a>
            <?php endif; ?>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Terug</a>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2><i class="fas fa-chart-line"></i> Spelers Rankings</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Plaats Dit Seizoen</th>
                        <th>Naam</th>
                        <th>Team</th>
                        <th>Gewonnen</th>
                        <th>Verloren</th>
                        <th>Punten</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $standings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $standing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="<?php echo e($standing['player_id'] == $player->id ? 'table-success' : ''); ?>">
                            <td><?php echo e($index + 1); ?></td>
                            <td><a href="<?php echo e(route('players.show', $standing['player_id'])); ?>"><?php echo e($standing['player_name']); ?></a></td>
                            <td><a href="<?php echo e(route('teams.show', $standing['team_id'])); ?>"><?php echo e($standing['team_name']); ?></a></td>
                            <td><?php echo e($standing['matches_won']); ?></td>
                            <td><?php echo e($standing['matches_lost']); ?></td>
                            <td><?php echo e($standing['points']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/players/show.blade.php ENDPATH**/ ?>