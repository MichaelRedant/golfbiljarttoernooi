<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Clubs</h1>
    <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
        <a href="<?php echo e(route('clubs.create')); ?>" class="btn btn-primary">Nieuwe Club</a>
    <?php endif; ?>
    <table class="table">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Locatie</th>
                <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
                <th>Acties</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $clubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $club): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><a href="<?php echo e(route('clubs.show', $club->id)); ?>"><?php echo e($club->name); ?></a></td>
                <td><?php echo e($club->location); ?></td>
                <td>
                    <?php if(auth()->user() && auth()->user()->role === 'admin'): ?>
                        <a href="<?php echo e(route('clubs.edit', $club->id)); ?>" class="btn btn-sm btn-info">Bewerken</a>
                        <form action="<?php echo e(route('clubs.destroy', $club->id)); ?>" method="POST" style="display: inline-block;">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-sm btn-danger">Verwijderen</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/clubs/index.blade.php ENDPATH**/ ?>