<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <h1 class="mb-4"><i class="fas fa-layer-group"></i> Kies een Divisie</h1>
    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-4 mb-4">
                <div class="card division-card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo e($division->name); ?></h5>
                        <?php if($currentSeasonId): ?>
                            <a href="<?php echo e(route('rankings.teams', ['division' => $division->id, 'season_id' => $currentSeasonId])); ?>" class="btn btn-primary btn-block  mt-2">
                                <i class="fas fa-users"></i> Team Rankings
                            </a>
                            <a href="<?php echo e(route('rankings.players', ['division' => $division->id, 'season_id' => $currentSeasonId])); ?>" class="btn btn-secondary btn-block  mt-2">
                                <i class="fas fa-user"></i> Player Rankings
                            </a>
                        <?php else: ?>
                            <p class="text-muted">Geen gespeelde wedstrijden gevonden.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <p class="text-center">Geen divisies gevonden.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/rankings/index.blade.php ENDPATH**/ ?>