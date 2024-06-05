<!-- resources/views/teams/create.blade.php -->



<?php $__env->startSection('content'); ?>
    <h1>Maak een Nieuw Team</h1>

    <!-- Formulier voor het maken van een nieuw team -->
    <form action="<?php echo e(route('teams.store')); ?>" method="POST">
        <?php echo csrf_field(); ?> <!-- Cross-site request forgery bescherming -->
        <div class="form-group">
            <label for="name">Team Name:</label>
            <input type="text" name="name" class="form-control" id="name" required>
        </div>
        <div class="form-group">
            <label for="division_id">Division:</label>
            <select name="division_id" class="form-control" id="division_id" required>
                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($division->id); ?>"><?php echo e($division->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/teams/create.blade.php ENDPATH**/ ?>