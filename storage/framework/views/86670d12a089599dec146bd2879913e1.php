<!-- resources/views/divisions/edit.blade.php -->



<?php $__env->startSection('content'); ?>
    <h1>Divisie Bewerken: <?php echo e($division->name); ?></h1>

    <form action="<?php echo e(route('divisions.update', $division)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div>
            <label for="name">Naam:</label>
            <input type="text" id="name" name="name" value="<?php echo e($division->name); ?>">
        </div>
        <button type="submit">Bijwerken</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/divisions/edit.blade.php ENDPATH**/ ?>