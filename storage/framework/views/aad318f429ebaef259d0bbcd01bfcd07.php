<?php $__env->startSection('content'); ?>
    <h1>Divisies</h1>

    <a href="<?php echo e(route('divisions.create')); ?>" class="btn btn-primary mb-2">Nieuwe Divisie Toevoegen</a>

    <?php if($divisions->isEmpty()): ?>
        <p>Er zijn geen divisies beschikbaar.</p>
    <?php else: ?>
        <table class="table">
            <thead>
                <tr>
                    <th class="text-justify">ID</th>
                    <th class="text-justify">Naam</th>
                    <th class="text-justify">Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td class="text-justify"><?php echo e($division->id); ?></td>
                        <td class="text-justify"><?php echo e($division->name); ?></td>
                        <td>
                            <a href="<?php echo e(route('divisions.show', $division)); ?>" class="btn btn-primary btn-sm">Bekijken</a>
                            <a href="<?php echo e(route('divisions.edit', $division)); ?>" class="btn btn-secondary btn-sm">Bewerken</a>
                            <a href="<?php echo e(route('divisions.destroy', $division)); ?>" class="btn btn-danger btn-sm"
                                onclick="event.preventDefault(); document.getElementById('delete-division-<?php echo e($division->id); ?>').submit();">
                                Verwijderen
                            </a>
                            

                            <form id="delete-division-<?php echo e($division->id); ?>" action="<?php echo e(route('divisions.destroy', $division)); ?>" method="POST" style="display: none;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/divisions/index.blade.php ENDPATH**/ ?>