<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h1 class="mb-4"><i class="fas fa-trophy"></i> Team Klassement</h1>

    <div class="card">
        <div class="card-body">
            <form action="<?php echo e(route('rankings.teams', ['division' => $division->id])); ?>" method="GET" class="mb-4">
                <div class="form-group">
                    <label for="season_id"><i class="fas fa-calendar-alt"></i> Kies een seizoen:</label>
                    <select id="season_id" name="season_id" class="form-control" onchange="this.form.submit()">
                        <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($season->id); ?>" <?php echo e($season->id == $seasonId ? 'selected' : ''); ?>>
                                <?php echo e($season->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </form>

            <table class="table table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Team</th>
                        <th>Gewonnen</th>
                        <th>Verloren</th>
                        <th>Gelijk</th>
                        <th>Punten</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $standings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $standing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($index + 1); ?></td>
                            <td><a href="<?php echo e(route('teams.show', ['team' => $standing['team_id']])); ?>"><?php echo e($standing['team_name']); ?></a></td>
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

    <div class="mt-4 text-center">
        <a href="<?php echo e(route('rankings.index')); ?>" class="btn btn-secondary btn-lg"><i class="fas fa-arrow-left"></i> Terug naar Overzicht</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/rankings/teams.blade.php ENDPATH**/ ?>