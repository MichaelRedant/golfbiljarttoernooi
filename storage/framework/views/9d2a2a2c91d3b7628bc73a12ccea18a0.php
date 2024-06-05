<!-- resources/views/divisions/create.blade.php -->



<?php $__env->startSection('content'); ?>
    <h1>Nieuwe Divisie Toevoegen</h1>

    <form action="<?php echo e(route('divisions.store')); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <div>
            <label for="name">Naam:</label>
            <input type="text" id="name" name="name">
        </div>
        <button type="submit">Toevoegen</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/divisions/create.blade.php ENDPATH**/ ?>