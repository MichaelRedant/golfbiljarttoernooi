<?php $__env->startSection('content'); ?>
<style>
    .highlight-row {
        background-color: #28a745 !important; /* Bootstrap success color */
        color: white !important;
    }
    </style>
<div class="container">
    <div class="card mb-4">
        <div class="card-header">
            <h1 class="d-flex align-items-center">
                <i class="fas fa-users mr-2"></i> Team Details - <?php echo e($team->name); ?>

            </h1>
        </div>
        <div class="card-body">
            <form action="<?php echo e(route('teams.show', $team)); ?>" method="GET">
                <div class="form-group">
                    <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
                    <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                        <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($season->id); ?>" <?php echo e($season->id == $currentSeasonId ? 'selected' : ''); ?>>
                                <?php echo e($season->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </form>
            <p><strong><i class="fas fa-layer-group"></i> Divisie:</strong> <?php echo e($team->division->name); ?></p>
            <p><strong><i class="fas fa-trophy"></i> Aantal Gewonnen:</strong> <?php echo e($currentTeamStanding['games_won']); ?></p>
            <p><strong><i class="fas fa-thumbs-down"></i> Aantal Verloren:</strong> <?php echo e($currentTeamStanding['games_lost']); ?></p>
            <p><strong><i class="fas fa-handshake"></i> Aantal Gelijk:</strong> <?php echo e($currentTeamStanding['games_draw']); ?></p>
            <p><strong><i class="fas fa-star"></i> Totaal Punten:</strong> <?php echo e($currentTeamStanding['points']); ?></p>
            <p><strong><i class="fas fa-medal"></i> Plaats dit seizoen:</strong> 
                <?php $__currentLoopData = $standings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $standing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($standing['team_id'] == $team->id): ?>
                        <?php echo e($index + 1); ?>

                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </p>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
            <a href="<?php echo e(route('teams.edit', $team)); ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Bewerk <?php echo e($team->name); ?>

            </a>
            <?php endif; ?>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Terug
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-chart-line"></i> Team Rankings</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Naam</th>
                        <th>Gewonnen</th>
                        <th>Verloren</th>
                        <th>Gelijk</th>
                        <th>Punten</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $standings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $standing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr <?php if($standing['team_id'] == $team->id): ?> class="highlight-row" <?php endif; ?>>
                            <td><?php echo e($index + 1); ?></td>
                            <td><a href="<?php echo e(route('teams.show', $standing['team_id'])); ?>"><?php echo e($standing['team_name']); ?></a></td>
                            <td><?php echo e($standing['games_won']); ?></td>
                            <td><?php echo e($standing['games_lost']); ?></td>
                            <td><?php echo e($standing['games_draw']); ?></td>
                            <td><?php echo e($standing['points']); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h2><i class="fas fa-users"></i> Spelerslijst</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Team</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><a href="<?php echo e(route('players.show', $player->id)); ?>"><?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?></a></td>
                            <td><?php echo e($team->name); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/teams/show.blade.php ENDPATH**/ ?>