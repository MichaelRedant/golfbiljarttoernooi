<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h1>Divisie: <?php echo e($division->name); ?></h1>

    <div class="mb-4">
        <form action="<?php echo e(route('divisions.show', $division->id)); ?>" method="GET">
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
    </div>

    <h2>Volgende Wedstrijddag</h2>
    <?php if($gamesByDate->isNotEmpty()): ?>
        <?php
            $upcomingDates = $gamesByDate->keys()->filter(function ($date) {
                return \Carbon\Carbon::parse($date) >= \Carbon\Carbon::today();
            });
            $nextDate = $upcomingDates->first();
        ?>
        <?php if($nextDate): ?>
            <div class="card" style="max-width: 600px;">
                <div class="card-header"><?php echo e(\Carbon\Carbon::parse($nextDate)->format('d-m-Y')); ?></div>
                <ul class="list-group list-group-flush">
                    <?php $__currentLoopData = $gamesByDate[$nextDate]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="list-group-item">
                            <?php if($game->home_team_id && $game->away_team_id): ?>
                                <a href="<?php echo e(route('teams.show', $game->homeTeam->id ?? '#')); ?>"><?php echo e($game->homeTeam->name ?? 'N/A'); ?></a>
                                tegen
                                <a href="<?php echo e(route('teams.show', $game->awayTeam->id ?? '#')); ?>"><?php echo e($game->awayTeam->name ?? 'N/A'); ?></a>
                                <span class="float-end"><?php echo e($game->home_score ?? ''); ?> : <?php echo e($game->away_score ?? ''); ?></span>
                                <i class="fas fa-home ml-4"></i><span class="ml-2"><small><?php echo e($game->homeTeam->location ?? 'N/A'); ?></small></span>
                                <?php if(auth()->check() && (auth()->user()->isAdmin() || (auth()->user()->isTeam() && auth()->user()->team_id == $game->home_team_id))): ?>
                                    <a href="<?php echo e(route('games.form', $game->id)); ?>" class="btn btn-sm btn-success float-end ms-2" style="background-color: #28a745; border-color: #28a745;"><i class="fas fa-play"></i> Start Wedstrijd</a>
                                <?php endif; ?>
                            <?php elseif($game->bye_team_id): ?>
                                <i class="fas fa-user-slash"></i>
                                <strong><?php echo e(optional($game->byeTeam)->name); ?> heeft een Bye</strong>
                            <?php else: ?>
                                Ongeplande tijd
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php else: ?>
            <p>Geen aankomende wedstrijden gepland voor dit seizoen.</p>
        <?php endif; ?>
    <?php else: ?>
        <p>Geen aankomende wedstrijden gepland voor dit seizoen.</p>
    <?php endif; ?>

    <h2>Standen</h2>
    <table class="table table-bordered table-striped">
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

    <h2>Voorbije Wedstrijden</h2>
    <?php if($gamesByDate->isNotEmpty()): ?>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Datum</th>
                    <th>Thuis Team</th>
                    <th>Uit Team</th>
                    <th>Uitslag</th>
                    <th>Actie</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $gamesByDate->sortKeysDesc(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $date => $gamesOnDate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(\Carbon\Carbon::parse($date) < \Carbon\Carbon::today()): ?>
                        <?php $__currentLoopData = $gamesOnDate; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(!$game->bye_team_id): ?>
                                <tr>
                                    <td><?php echo e(\Carbon\Carbon::parse($date)->format('d-m-Y')); ?></td>
                                    <td><a href="<?php echo e(route('teams.show', $game->homeTeam->id ?? '#')); ?>"><?php echo e($game->homeTeam ? $game->homeTeam->name : 'Bye'); ?></a></td>
                                    <td><a href="<?php echo e(route('teams.show', $game->awayTeam->id ?? '#')); ?>"><?php echo e($game->awayTeam ? $game->awayTeam->name : 'Bye'); ?></a></td>
                                    <td><?php echo e($game->home_score ?? ''); ?> : <?php echo e($game->away_score ?? ''); ?></td>
                                    <td><a href="<?php echo e(route('games.show', $game->id)); ?>" class="btn btn-primary"><i class="fas fa-eye"></i> Wedstrijd bekijken</a></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Geen voorbije wedstrijden dit seizoen.</p>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/divisions/show.blade.php ENDPATH**/ ?>