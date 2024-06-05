<?php $__env->startSection('content'); ?>
<div class="container">
    <h1 class="mb-4"><i class="fas fa-users"></i> Teams</h1>

    <!-- Search input and division selection form -->
    <form action="<?php echo e(route('teams.index')); ?>" method="GET">
        <div class="mb-3">
            <label for="searchInput" class="form-label"><i class="fas fa-search"></i> Zoek teams:</label>
            <input type="text" id="searchInput" name="search" class="form-control" placeholder="Voer teamnaam in..." value="<?php echo e(request('search')); ?>">
            <small class="form-text text-muted">Klik op "Toon Teams" om de zoekresultaten te zien.</small>
        </div>
        <div class="mb-3">
            <label for="division" class="form-label"><i class="fas fa-layer-group"></i> Selecteer een divisie:</label>
            <select id="division" name="division" class="form-select form-control">
                <option value="">Alle divisies</option>
                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($division->id); ?>" <?php echo e(request('division') == $division->id ? 'selected' : ''); ?>><?php echo e($division->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Toon Teams</button>
    </form>

    <?php if($teams->isNotEmpty()): ?>
        <div class="mt-4">
            <h2><?php echo e($divisionName ?? 'Geselecteerde divisie'); ?></h2>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-users"></i> Naam</th>
                                    <th><i class="fas fa-map-marker-alt"></i> Locatie</th>
                                    <th><i class="fas fa-building"></i> Club</th> <!-- New column for Club -->
                                    <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
                                        <th><i class="fas fa-cogs"></i> Acties</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><a href="<?php echo e(route('teams.show', $team)); ?>"><?php echo e($team->name); ?></a></td>
                                        <td><?php echo e($team->location); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('clubs.show', $team->club->id)); ?>"><?php echo e($team->club->name); ?></a>
                                        </td> <!-- Club name with clickable link -->
                                        <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
                                            <td>
                                                <a href="<?php echo e(route('teams.edit', $team)); ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> Bewerken</a>
                                                <form action="<?php echo e(route('teams.destroy', $team)); ?>" method="POST" style="display: inline;">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Weet je zeker dat je dit team wilt verwijderen?')">
                                                        <i class="fas fa-trash-alt"></i> Verwijderen
                                                    </button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>Geen teams gevonden.</p>
    <?php endif; ?>
  
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/teams/index.blade.php ENDPATH**/ ?>