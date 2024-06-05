<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Wedstrijdformulier voor <?php echo e($game->homeTeam->name); ?> vs <?php echo e($game->awayTeam->name); ?></h1>

    <div class="card my-4">
        <div class="card-body">
            <h5 class="card-title">Wedstrijdinformatie</h5>
            <p><strong>Thuisploeg:</strong> <?php echo e($game->homeTeam->name); ?></p>
            <p><strong>Bezoekers:</strong> <?php echo e($game->awayTeam->name); ?></p>
            <p><strong>Datum:</strong> <?php echo e($game->date->format('Y-m-d')); ?></p>
        </div>
    </div>

    <form action="<?php echo e(route('games.update', $game->id)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
    
        <!-- Hidden fields for team IDs -->
        <input type="hidden" name="home_team_id" value="<?php echo e($game->homeTeam->id); ?>">
        <input type="hidden" name="away_team_id" value="<?php echo e($game->awayTeam->id); ?>">

    
        <div class="card">
            <div class="card-header">Wedstrijdscore</div>
            <div class="card-body">
                <div class="form-group">
                    <label>Thuis score:</label>
                    <input type="text" class="form-control" id="home_score" value="<?php echo e($game->home_score ?? '0'); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Uit score:</label>
                    <input type="text" class="form-control" id="away_score" value="<?php echo e($game->away_score ?? '0'); ?>" readonly>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Kapiteins en reservespelers</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label>Kapitein <?php echo e($game->homeTeam->name); ?></label>
                        <select class="form-control" name="home_captain">
                            <?php $__currentLoopData = $game->homeTeam->players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($player->id); ?>" <?php echo e($player->id == optional($game->homeTeam->captain)->id ? 'selected' : ''); ?>>
                                    <?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Kapitein <?php echo e($game->awayTeam->name); ?></label>
                        <select class="form-control" name="away_captain">
                            <?php $__currentLoopData = $game->awayTeam->players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($player->id); ?>" <?php echo e($player->id == optional($game->awayTeam->captain)->id ? 'selected' : ''); ?>>
                                    <?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Reservespeler <?php echo e($game->homeTeam->name); ?></label>
                        <select class="form-control" name="home_reserve">
                            <?php $__currentLoopData = $game->homeTeam->players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($player->id); ?>" <?php echo e($player->id == optional($game->homeTeam->reserve)->id ? 'selected' : ''); ?>>
                                    <?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Reservespeler <?php echo e($game->awayTeam->name); ?></label>
                        <select class="form-control" name="away_reserve">
                            <?php $__currentLoopData = $game->awayTeam->players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($player->id); ?>" <?php echo e($player->id == optional($game->awayTeam->reserve)->id ? 'selected' : ''); ?>>
                                    <?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Manches en scores -->
        <div class="card">
            <div class="card-header">Spelers en Scores</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Spelers Thuisploeg</th>
                                <th>Spelers Bezoekers</th>
                                <th>1M</th>
                                <th>2M</th>
                                <th>Belle</th>
                                <th>Uitslag</th>
                                <th>Acties</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for($i = 0; $i < 6; $i++): ?>
                            <tr>
                                <td>
                                    <select class="form-control" name="scores[<?php echo e($i); ?>][home_player]">
                                        <?php $__currentLoopData = $game->homeTeam->players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($player->id); ?>"><?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td>
                                    <select class="form-control" name="scores[<?php echo e($i); ?>][away_player]">
                                        <?php $__currentLoopData = $game->awayTeam->players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($player->id); ?>"><?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" class="form-control manche" name="scores[<?php echo e($i); ?>][1M]" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control manche" name="scores[<?php echo e($i); ?>][2M]" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control belle" name="scores[<?php echo e($i); ?>][Belle]" disabled>
                                </td>
                                <td>
                                    <input type="text" class="form-control result" readonly>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-secondary lockMatch">Afsluiten</button>
                                    <button type="button" class="btn btn-primary unlockMatch" disabled>Bewerken</button>
                                </td>
                            </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table
                    </div>
            </div>
        </div>
    
        <div class="text-center mt-4 mb-4">
            <button type="submit" class="btn btn-primary">Wedstrijd opslaan</button>
        </div
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const homeScoreInput = document.getElementById('home_score');
        const awayScoreInput = document.getElementById('away_score');
        const rows = document.querySelectorAll('tbody tr');
        const form = document.querySelector('form');
    
    form.addEventListener('submit', function() {
        // Schakel alle disabled inputs in zodat ze worden meegestuurd
        document.querySelectorAll('input[disabled], select[disabled]').forEach(input => {
            input.disabled = false;
        });
    });
    
        function updateResults() {
            let homeWins = 0;
            let awayWins = 0;
    
            rows.forEach(row => {
                const manche1Input = row.querySelector('input[name*="[1M]"]');
                const manche2Input = row.querySelector('input[name*="[2M]"]');
                const belleInput = row.querySelector('input[name*="[Belle]"]');
                const resultInput = row.querySelector('input.result');
    
                let homePoints = 0;
                let awayPoints = 0;
    
                if (parseInt(manche1Input.value) === 1) homePoints++;
                if (parseInt(manche2Input.value) === 2) awayPoints++;
                if (parseInt(manche1Input.value) === 2) awayPoints++;
                if (parseInt(manche2Input.value) === 1) homePoints++;
    
                // Belle-input inschakelen als er een gelijkspel is na de eerste twee manches
                if (homePoints === awayPoints) {
                    belleInput.disabled = false;
                } else {
                    belleInput.disabled = true;
                    belleInput.value = ""; // Reset belle input if not a draw
                }
    
                if (belleInput.value === "1") homePoints++;
                if (belleInput.value === "2") awayPoints++;
    
                resultInput.value = `${homePoints} - ${awayPoints}`;
    
                // Bepalen wie de match wint en de wedstrijdscore updaten
                if (homePoints > awayPoints) homeWins++;
                if (awayPoints > homePoints) awayWins++;
            });
    
            homeScoreInput.value = homeWins;
            awayScoreInput.value = awayWins;
        }
    
        rows.forEach(row => {
            const inputs = row.querySelectorAll('.manche, .belle');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    updateResults();
                });
            });
    
            row.querySelector('.lockMatch').addEventListener('click', function() {
                const inputs = row.querySelectorAll('input, select');
                inputs.forEach(input => input.disabled = true);
                this.disabled = true;
                row.querySelector('.unlockMatch').disabled = false;
            });
    
            row.querySelector('.unlockMatch').addEventListener('click', function() {
                const inputs = row.querySelectorAll('input, select:not(.result)');
                inputs.forEach(input => input.disabled = false);
                this.disabled = true;
                row.querySelector('.lockMatch').disabled = false;
            });
        });
    
        updateResults(); // Initial update on page load
    });
    </script>
    
    
    
    
    
    


<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/games/match_form.blade.php ENDPATH**/ ?>