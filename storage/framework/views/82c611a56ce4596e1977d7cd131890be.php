<?php $__env->startSection('title', 'Nieuw Seizoen Toevoegen'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Nieuw Seizoen Toevoegen</h1>
    <form method="POST" action="<?php echo e(route('seasons.store')); ?>">
        <?php echo csrf_field(); ?>
        
        <div class="form-group">
            <label for="name">Seizoensnaam</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <!-- Datumkiezer voor de startdatum van het seizoen -->
        <div class="form-group">
            <label for="start_date">Startdatum</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>

        <div class="form-group">
            <label for="end_date">Einddatum</label>
            <input type="date" class="form-control" id="end_date" name="end_date" required>
        </div>
        

        <button type="submit" class="btn btn-primary">Opslaan</button>
        <a href="<?php echo e(route('seasons.index')); ?>" class="btn btn-secondary">Terug</a>
    </form>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/seasons/create.blade.php ENDPATH**/ ?>