<?php $__env->startSection('title', 'Seizoenbeheer'); ?>

<?php $__env->startSection('content'); ?>
<div class="container">
    <h1>Seizoenbeheer</h1>

    <!-- Knop om nieuw seizoen toe te voegen -->
    <a href="<?php echo e(route('seasons.create')); ?>" class="btn btn-primary mb-3">Nieuw Seizoen Toevoegen</a>

    <!-- Seizoenen lijst -->
    <table class="table">
        <thead>
            <tr>
                <th>Naam</th>
                <th>Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $seasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $season): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($season->name); ?></td>
                <td>
                    <a href="<?php echo e(route('seasons.edit', $season)); ?>" class="btn btn-info">Bewerken</a>
                    <a href="<?php echo e(route('games.generate', ['season_id' => $season->id])); ?>" class="btn btn-success">Genereer Wedstrijden</a>
                    <form action="<?php echo e(route('seasons.destroy', $season)); ?>" method="POST" style="display:inline;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="btn btn-danger" onclick="return confirmDelete()">Verwijderen</button>
                    </form>
                    
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
        </tbody>
    </table>
</div>
<?php $__env->startSection('scripts'); ?>
<script>
    function confirmDelete() {
        return confirm('Weet je zeker dat je dit seizoen wilt verwijderen? Dit zal alle bijbehorende wedstrijden en data verwijderen.');
    }
</script>
<?php $__env->stopSection(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/seasons/index.blade.php ENDPATH**/ ?>